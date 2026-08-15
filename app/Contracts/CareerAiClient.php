<?php

namespace App\Contracts;

interface CareerAiClient
{
    /**
     * Send a prepared student profile payload for career analysis.
     */
    public function recommend(array $payload): array;
}