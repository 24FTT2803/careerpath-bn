<?php

namespace App\Services\AI;

use App\Contracts\CareerAdviserClient;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CareerAdviserService
{
    public function __construct(
        private CareerAdviserClient $careerAdviser,
        private CareerAdviserContextBuilder $contextBuilder
    ) {
    }

    /**
     * Ask the Career Adviser a question using the
     * student's current CareerPath context.
     */
    public function ask(
        User $student,
        string $message
    ): array {
        $context = $this->contextBuilder->build(
            $student
        );

        $response = $this->careerAdviser->ask(
            $context,
            trim($message)
        );

        return $this->validateResponse(
            $response
        );
    }

    /**
     * Validate the structured response returned by
     * the configured Career Adviser client.
     */
    private function validateResponse(
        array $response
    ): array {
        return Validator::make(
            $response,
            [
                'schema_version' => [
                    'required',
                    'string',
                ],

                'status' => [
                    'required',
                    'in:completed',
                ],

                'message' => [
                    'required',
                    'string',
                    'max:5000',
                ],
            ]
        )->validate();
    }
}