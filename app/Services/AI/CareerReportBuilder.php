<?php

namespace App\Services\AI;

use App\Models\AcademicRecord;
use App\Models\RecommendationGeneration;
use App\Models\StudentAspiration;
use App\Models\StudentCertification;
use App\Models\StudentCompetency;
use App\Models\StudentInterest;
use App\Models\StudentProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CareerReportBuilder
{
    /**
     * Assemble everything the career report needs.
     *
     * The report is one document covering profile and career
     * recommendations. Passing a generation renders it as it
     * stood for that generation instead of today.
     *
     * The report template reads the student's relations
     * directly, so frozen data is supplied by replacing those
     * relations rather than by rewriting the template. The
     * markup cannot tell the difference.
     *
     * @return array<string, mixed>
     */
    public function for(
        User $student,
        ?RecommendationGeneration $generation = null
    ): array {
        $student->load([
            'profile',
            'academicRecords',
            'competencies',
            'interests',
            'projects',
            'certifications',
            'aspirations',
            'milestones',
        ]);

        $generation ??= $student
            ->currentRecommendationGeneration()
            ->first();

        $this->applyRecommendations(
            $student,
            $generation
        );

        $snapshot = $generation?->profileSnapshot;

        if ($snapshot !== null) {
            $this->applySnapshot(
                $student,
                $snapshot->snapshot_data ?? []
            );
        }

        return [
            'user' => $student,
            'generation' => $generation,

            /*
             * An archived report must be dated when its
             * recommendations were produced, not when the file
             * happened to be downloaded.
             */
            'reportDate' => $generation?->generated_at ?? now(),

            /*
             * False for generations backfilled from rows written
             * before snapshots existed. The profile shown is
             * today's, which the template notes.
             */
            'snapshotAvailable' => $snapshot !== null,

            'profileCompletion' => $student->profile_completion,
            'readinessScore' => $student->readiness_score ?? 0,
        ];
    }

    /**
     * Point the report at one generation's recommendations.
     */
    private function applyRecommendations(
        User $student,
        ?RecommendationGeneration $generation
    ): void {
        $recommendations = $generation === null
            ? new Collection
            : $generation
                ->recommendations()
                ->with([
                    'career',
                    'jobRole.subSector',
                ])
                ->get();

        $student->setRelation(
            'currentRecommendations',
            $recommendations
        );
    }

    /**
     * Replace the AI-relevant profile with the snapshot.
     *
     * Identity details such as name and contact are not part of
     * the snapshot and stay live, so correcting a name updates
     * every report immediately.
     *
     * @param  array<string, mixed>  $snapshot
     */
    private function applySnapshot(
        User $student,
        array $snapshot
    ): void {
        $student->programme = $snapshot['programme']
            ?? $student->programme;

        $student->cgpa = $snapshot['cgpa']
            ?? $student->cgpa;

        $student->setRelation(
            'academicRecords',
            $this->hydrateMany(
                AcademicRecord::class,
                $snapshot['academic_records'] ?? []
            )
        );

        $student->setRelation(
            'competencies',
            $this->hydrateMany(
                StudentCompetency::class,
                $snapshot['competencies'] ?? []
            )
        );

        $student->setRelation(
            'interests',
            $this->hydrateMany(
                StudentInterest::class,
                $snapshot['interests'] ?? []
            )
        );

        $student->setRelation(
            'projects',
            $this->hydrateMany(
                StudentProject::class,
                $snapshot['projects'] ?? []
            )
        );

        $student->setRelation(
            'certifications',
            $this->hydrateMany(
                StudentCertification::class,
                $snapshot['certifications'] ?? []
            )
        );

        $student->setRelation(
            'aspirations',
            isset($snapshot['aspirations'])
                && is_array($snapshot['aspirations'])
                    ? $this->hydrate(
                        StudentAspiration::class,
                        $snapshot['aspirations']
                    )
                    : null
        );
    }

    /**
     * Build unsaved models from snapshot rows.
     *
     * @param  class-string<Model>  $model
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function hydrateMany(
        string $model,
        array $rows
    ): Collection {
        return new Collection(
            array_map(
                fn (array $row) => $this->hydrate($model, $row),
                array_values($rows)
            )
        );
    }

    /**
     * Build one unsaved model from a snapshot row.
     *
     * forceFill bypasses mass assignment rules, which exist to
     * guard request input. This data came from our own snapshot.
     * Date columns are cast by the model, so strings stored as
     * Y-m-d become Carbon instances again.
     *
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $attributes
     */
    private function hydrate(
        string $model,
        array $attributes
    ): Model {
        return (new $model)->forceFill($attributes);
    }
}
