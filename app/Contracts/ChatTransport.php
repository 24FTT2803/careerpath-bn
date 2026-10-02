<?php

namespace App\Contracts;

/**
 * A chat-completions endpoint.
 *
 * Both career clients send the same shape of request — messages,
 * an optional JSON schema, an optional temperature — and differ
 * only in what they ask for. Putting that behind a contract means
 * changing inference provider does not touch the prompts, the
 * schemas, or anything that decides what a student is told.
 */
interface ChatTransport
{
    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>|null  $responseFormat
     * @param  float|null  $temperature  How freely the model may
     *                                   phrase its answer. Lower
     *                                   keeps it closer to the
     *                                   supplied wording.
     * @return array{
     *     content: string,
     *     model: string|null,
     *     usage?: array<string, mixed>|null
     * }
     */
    public function chat(
        array $messages,
        ?array $responseFormat = null,
        ?float $temperature = null
    ): array;
}
