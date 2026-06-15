<?php

namespace App\Contracts\Ai;

interface AiProvider
{
    public function name(): string;

    /**
     * @param  array<string, mixed>  $context
     * @return array{
     *     executive_summary: string,
     *     interpretation: string,
     *     limitations: string,
     *     recommendations: string,
     * }
     */
    public function interpret(array $context): array;
}
