<?php

namespace App\Services;

use App\Enums\DataFileFormat;
use App\Models\DataFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use RuntimeException;

class SpreadsheetReaderService
{
    public function sheetNames(DataFile $dataFile): array
    {
        if ($dataFile->format === DataFileFormat::Csv) {
            return [];
        }

        $localPath = $this->materializeLocalCopy($dataFile);

        try {
            $reader = IOFactory::createReaderForFile($localPath);
            $reader->setReadDataOnly(true);

            return $reader->listWorksheetNames($localPath);
        } finally {
            $this->cleanupLocalCopy($localPath);
        }
    }

    /**
     * @return array{
     *     headers: list<string>,
     *     rows: list<list<string|null>>,
     *     quality_warnings: list<array{column: string, type: string, message: string}>,
     * }
     */
    public function preview(DataFile $dataFile, ?string $sheetName = null, int $rowLimit = 10): array
    {
        $localPath = $this->materializeLocalCopy($dataFile);

        try {
            $rows = match ($dataFile->format) {
                DataFileFormat::Csv => $this->readCsvRows($localPath, $rowLimit + 1),
                default => $this->readSpreadsheetRows($localPath, $sheetName ?? $dataFile->sheet_name, $rowLimit + 1),
            };
        } finally {
            $this->cleanupLocalCopy($localPath);
        }

        if ($rows === []) {
            throw new RuntimeException('The uploaded file does not contain any data rows.');
        }

        $headers = array_map(
            fn ($value) => trim((string) $value),
            array_shift($rows),
        );

        if ($headers === [] || $headers === ['']) {
            throw new RuntimeException('The uploaded file is missing a header row.');
        }

        return [
            'headers' => $headers,
            'rows' => array_slice($rows, 0, $rowLimit),
            'quality_warnings' => $this->detectQualityWarnings($headers, $rows),
        ];
    }

    public function validateStructure(DataFile $dataFile): void
    {
        $this->preview($dataFile, $dataFile->sheet_name, 1);
    }

    private function readCsvRows(string $path, int $limit): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read the CSV file.');
        }

        while (($data = fgetcsv($handle)) !== false && count($rows) < $limit) {
            $rows[] = array_map(
                fn ($value) => $value === null ? null : (string) $value,
                $data,
            );
        }

        fclose($handle);

        return $rows;
    }

    private function readSpreadsheetRows(string $path, ?string $sheetName, int $limit): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class($limit) implements IReadFilter
        {
            public function __construct(private readonly int $maxRow) {}

            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row <= $this->maxRow;
            }
        });

        $spreadsheet = $reader->load($path);
        $worksheet = $sheetName !== null
            ? $spreadsheet->getSheetByName($sheetName)
            : $spreadsheet->getActiveSheet();

        if ($worksheet === null) {
            throw new RuntimeException('The requested worksheet could not be found.');
        }

        $rows = [];
        foreach ($worksheet->getRowIterator() as $row) {
            if (count($rows) >= $limit) {
                break;
            }

            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue() === null ? null : (string) $cell->getValue();
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|null>>  $rows
     * @return list<array{column: string, type: string, message: string}>
     */
    private function detectQualityWarnings(array $headers, array $rows): array
    {
        $warnings = [];

        foreach ($headers as $index => $header) {
            $values = array_map(
                fn (array $row) => $row[$index] ?? null,
                $rows,
            );

            $missingCount = count(array_filter(
                $values,
                fn ($value) => $value === null || trim((string) $value) === '',
            ));

            if ($missingCount > 0) {
                $warnings[] = [
                    'column' => $header,
                    'type' => 'missing_values',
                    'message' => "{$missingCount} missing value(s) detected in preview sample.",
                ];
            }

            $nonEmpty = array_values(array_filter(
                $values,
                fn ($value) => $value !== null && trim((string) $value) !== '',
            ));

            $numericCount = count(array_filter($nonEmpty, fn ($value) => is_numeric($value)));
            $dateCount = count(array_filter($nonEmpty, fn ($value) => strtotime((string) $value) !== false));

            if ($nonEmpty !== [] && $numericCount > 0 && $numericCount < count($nonEmpty) && $dateCount < count($nonEmpty)) {
                $warnings[] = [
                    'column' => $header,
                    'type' => 'type_inconsistency',
                    'message' => 'Mixed data types detected in preview sample.',
                ];
            }
        }

        return $warnings;
    }

    private function materializeLocalCopy(DataFile $dataFile): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'doctorstats_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to create a temporary file for processing.');
        }

        $stream = Storage::disk(config('filesystems.default'))->readStream($dataFile->s3_path);

        if ($stream === null) {
            throw new RuntimeException('Unable to read the uploaded file from storage.');
        }

        $destination = fopen($tempPath, 'w+b');

        if ($destination === false) {
            fclose($stream);
            throw new RuntimeException('Unable to open temporary file for writing.');
        }

        stream_copy_to_stream($stream, $destination);
        fclose($stream);
        fclose($destination);

        return $tempPath;
    }

    private function cleanupLocalCopy(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
