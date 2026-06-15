<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicAiProvider implements AiProvider
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function interpret(array $context): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->buildPrompt($context),
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Anthropic API request failed: '.$response->body());
        }

        $text = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        return $this->parseStructuredResponse($text);
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

        if (! is_array($decoded)) {
            if (preg_match('/\{.*\}/s', $text, $matches) === 1) {
                $decoded = json_decode($matches[0], true);
            }
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
