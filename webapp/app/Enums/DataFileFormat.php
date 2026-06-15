<?php

namespace App\Enums;

enum DataFileFormat: string
{
    case Xlsx = 'xlsx';
    case Xls = 'xls';
    case Csv = 'csv';

    public static function fromExtension(string $extension): ?self
    {
        return match (strtolower($extension)) {
            'xlsx' => self::Xlsx,
            'xls' => self::Xls,
            'csv' => self::Csv,
            default => null,
        };
    }
}
