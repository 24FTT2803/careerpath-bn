<?php

namespace App\Contracts;

interface CareerAdviserClient
{
    /**
     * Send a student's CareerPath context and question
     * to the configured Career Adviser client.
     */
    public function ask(
        array $context,
        string $message
    ): array;
}