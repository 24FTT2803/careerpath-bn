<?php

namespace App\Services\AI;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;

class CareerAiPayloadBuilder
{
    /**
     * Build the student profile payload that will be sent to the Career AI API.
     */
    public function build(User $student): array
    {
        $student->loadMissing([
            'academicRecords',
            'competencies',
            'interests',
            'projects',
            'certifications',
            'aspirations',
        ]);

        return [
            'schema_version' => '1.0',

            'student_profile' => [
                'programme' => $student->programme,

                'cgpa' => $student->cgpa !== null
                    ? (float) $student->cgpa
                    : null,

                'academic_records' => $student->academicRecords
                    ->map(fn ($record) => [
                        'institution_name' => $record->institution_name,
                        'programme_name' => $record->programme_name,
                        'level' => $record->level,
                        'start_date' => $this->formatDate($record->start_date),
                        'end_date' => $this->formatDate($record->end_date),

                        'cgpa' => $record->cgpa !== null
                            ? (float) $record->cgpa
                            : null,

                        'subjects' => $this->normaliseArray($record->subjects),
                        'grades' => $this->normaliseArray($record->grades),
                        'achievements' => $record->achievements,
                        'is_current' => (bool) $record->is_current,
                    ])
                    ->values()
                    ->all(),

                'competencies' => $student->competencies
                    ->map(fn ($competency) => [
                        'skill_name' => $competency->skill_name,
                        'category' => $competency->category,
                        'proficiency_level' => $competency->proficiency_level,
                        'evidence' => $this->normaliseArray($competency->evidence),
                        'description' => $competency->description,
                    ])
                    ->values()
                    ->all(),

                'interests' => $student->interests
                    ->map(fn ($interest) => [
                        'interest_name' => $interest->interest_name,
                        'category' => $interest->category,
                        'priority' => (int) $interest->priority,
                        'description' => $interest->description,
                    ])
                    ->values()
                    ->all(),

                'projects' => $student->projects
                    ->map(fn ($project) => [
                        'title' => $project->title,
                        'description' => $project->description,
                        'technologies_used' => $this->normaliseArray(
                            $project->technologies_used
                        ),
                        'role' => $project->role,
                        'start_date' => $this->formatDate($project->start_date),
                        'end_date' => $this->formatDate($project->end_date),
                        'achievements' => $project->achievements,
                    ])
                    ->values()
                    ->all(),

                'certifications' => $student->certifications
                    ->map(fn ($certification) => [
                        'certification_name' => $certification->certification_name,
                        'issuing_organization' => $certification->issuing_organization,
                        'issue_date' => $this->formatDate(
                            $certification->issue_date
                        ),
                        'expiry_date' => $this->formatDate(
                            $certification->expiry_date
                        ),
                        'description' => $certification->description,
                    ])
                    ->values()
                    ->all(),

                'aspirations' => $student->aspirations
                    ? [
                        'career_goals' => $this->normaliseArray(
                            $student->aspirations->career_goals
                        ),

                        'preferred_industries' => $this->normaliseArray(
                            $student->aspirations->preferred_industries
                        ),

                        'preferred_work_activities' => $this->normaliseArray(
                            $student->aspirations->preferred_work_activities
                        ),

                        'vision_statement' =>
                            $student->aspirations->vision_statement,

                        'mission_statement' =>
                            $student->aspirations->mission_statement,

                        'long_term_goals' =>
                            $student->aspirations->long_term_goals,
                    ]
                    : null,
            ],
        ];
    }

    /**
     * Ensure JSON database fields always become PHP arrays.
     */
    private function normaliseArray(mixed $value): array
    {
        if ($value instanceof Collection) {
            return $value->values()->all();
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return [$value];
        }

        return [];
    }

    /**
     * Convert database dates into a consistent API-friendly format.
     */
    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && trim($value) !== '') {
            return substr($value, 0, 10);
        }

        return null;
    }
}