<?php

namespace App\Services\AI;

use App\Models\ProfileSnapshot;
use App\Models\User;

class ProfileSnapshotService
{
    public function __construct(
        private CareerAiPayloadBuilder $payloadBuilder
    ) {}

    /**
     * Build the AI-relevant profile for a student.
     *
     * This deliberately reuses the payload builder rather than
     * defining a second idea of what counts as AI-relevant.
     * Outdated then means precisely: the AI would now be given
     * different input.
     *
     * Only the student_profile section is captured. The BIICF
     * role data supplied to Groq is not student data, and
     * including it would mark every student outdated whenever an
     * administrator edited the framework.
     *
     * @return array<string, mixed>
     */
    public function buildFor(User $student): array
    {
        $payload = $this->payloadBuilder->build($student);

        return $payload['student_profile'] ?? [];
    }

    /**
     * Hash a snapshot payload.
     *
     * @param  array<string, mixed>  $snapshotData
     */
    public function hash(array $snapshotData): string
    {
        return hash(
            'sha256',
            $this->canonicalJson($snapshotData)
        );
    }

    /**
     * Hash the student's profile as it stands right now.
     */
    public function currentHashFor(User $student): string
    {
        return $this->hash(
            $this->buildFor($student)
        );
    }

    /**
     * Store the student's current profile as a snapshot.
     *
     * An unchanged profile reuses the existing snapshot rather
     * than writing an identical row for every generation.
     */
    public function captureFor(User $student): ProfileSnapshot
    {
        $snapshotData = $this->buildFor($student);

        return ProfileSnapshot::firstOrCreate(
            [
                'user_id' => $student->id,
                'snapshot_hash' => $this->hash($snapshotData),
            ],
            [
                'snapshot_data' => $snapshotData,
            ]
        );
    }

    /**
     * Determine whether a snapshot still represents the
     * student's current AI-relevant profile.
     *
     * A missing snapshot returns true so that generations
     * without recorded profile data are never wrongly reported
     * as outdated.
     */
    public function matchesCurrentProfile(
        User $student,
        ?ProfileSnapshot $snapshot
    ): bool {
        if ($snapshot === null) {
            return true;
        }

        return $snapshot->snapshot_hash
            === $this->currentHashFor($student);
    }

    /**
     * Encode a snapshot so that equal profiles always produce
     * an identical string.
     *
     * Object keys are sorted because their order carries no
     * meaning. List order is preserved because the AI receives
     * the payload in that order, so a reordered list genuinely
     * is different input.
     *
     * @param  array<string, mixed>  $snapshotData
     */
    private function canonicalJson(array $snapshotData): string
    {
        $encoded = json_encode(
            $this->sortObjectKeys($snapshotData),
            JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_PRESERVE_ZERO_FRACTION
        );

        return $encoded === false
            ? ''
            : $encoded;
    }

    /**
     * Recursively sort associative array keys.
     *
     * @param  array<mixed, mixed>  $data
     * @return array<mixed, mixed>
     */
    private function sortObjectKeys(array $data): array
    {
        $isList = array_is_list($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sortObjectKeys($value);
            }
        }

        if (! $isList) {
            ksort($data);
        }

        return $data;
    }
}
