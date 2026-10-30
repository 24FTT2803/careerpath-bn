<?php

namespace App\Contracts;

interface CpbnBotClient
{
    /**
     * Answer a question about using CareerPath BN.
     *
     * @param  array<string, mixed>  $context  From CpbnBotContextBuilder.
     * @param  array<int, array{role: string, content: string}>  $history  Earlier messages, oldest first.
     * @return array{schema_version: string, status: string, message: string, links: array<int, string>, usage?: array<string, mixed>|null}
     */
    public function ask(array $context, array $history, string $message): array;
}
