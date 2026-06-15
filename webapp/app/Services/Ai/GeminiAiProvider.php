<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'gemini';
    }

    public function interpret(array $context): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $response = Http::timeout(120)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $this->buildPrompt($context)],
                        ],
                    ],
                ],
            ],
        );

        if (! $response->successful()) {
            throw new RuntimeException('Gemini API request failed: '.$response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text', '');

        return $this->parseStructuredResponse((string) $text);
    }

    private function buildPrompt(array $context): string
    {
        $payload = json_encode($context, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are a clinical biostatistics assistant. Analyze the following study context and statistical output.

Return valid JSON with exactly these keys:
- executive_summary
- interpretation
- limitations
- recommendations

Context:
{$payload}
PROMPT;
    }

    /**
     * @return array{executive_summary: string, interpretation: string, limitations: string, recommendations: string}
     */
    private function parseStructuredResponse(string $text): array
    {
        $decoded = json_decode(trim($text), true);

        if (! is_array($decoded) && preg_match('/\{.*\}/s', $text, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
        }

        if (! is_array($decoded)) {
            return [
                'executive_summary' => $text,
                'interpretation' => $text,
                'limitations' => 'AI response could not be structured automatically.',
                'recommendations' => 'Review the statistical output with a domain expert.',
            ];
        }

        return [
            'executive_summary' => (string) ($decoded['executive_summary'] ?? ''),
            'interpretation' => (string) ($decoded['interpretation'] ?? ''),
            'limitations' => (string) ($decoded['limitations'] ?? ''),
            'recommendations' => (string) ($decoded['recommendations'] ?? ''),
        ];
    }
}
