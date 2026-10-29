<?php

namespace App\Services\AI;

use App\Models\AcademicRecord;
use App\Models\BiicfBdqfLevel;
use App\Models\BiicfCompetency;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\StudentMilestone;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Everything the Career Adviser is allowed to know.
 *
 * The adviser is told to state nothing as fact that this context
 * does not hold, which is the right rule and was also why it had
 * no answer to the questions students actually asked. It held the
 * framework's role titles and competency names, and nothing that
 * says what a role asks for: no BDQF ladder, no entry
 * requirements, no per-tier proficiency, no progression — and the
 * student's own qualifications arrived without their BDQF level,
 * so it could not even say where the student stood. Nor did it
 * hold their milestones, which are the goals they have set
 * themselves and the most direct thing it could advise on.
 *
 * It now holds all of that, at two depths. The student's
 * recommended roles go in full — every tier, every entry
 * requirement, every required level, the trainings and where the
 * role leads. Every other role goes as one compact line: its
 * levels, the BDQF level it can be entered at, and its
 * progression. Sending all thirty-eight in full would add around
 * sixty thousand tokens to every question for detail that is
 * almost never asked about; this adds around eight.
 */
class CareerAdviserContextBuilder
{
    /**
     * What BDQF stands for, as the framework's Annex 1 names it.
     */
    public const BDQF_NAME = 'Brunei Darussalam Qualifications Framework';

    public function __construct(
        private CareerAiPayloadBuilder $profileBuilder
    ) {}

    /**
     * Build the CareerPath context available to the Career Adviser.
     *
     * @return array<string, mixed>
     */
    public function build(User $student): array
    {
        /*
         * Reuse the Career AI profile builder so both
         * recommendation and Adviser features receive the
         * same representation of the student's profile.
         */
        $profilePayload = $this->profileBuilder->build(
            $student
        );

        $studentProfile = $profilePayload['student_profile'] ?? [];

        $qualifications = $this->qualifications($student);

        /*
         * Replaced rather than added beside: the profile builder's
         * academic records omit the BDQF level, and the adviser
         * holding the same qualifications twice with different
         * fields would be two answers to "what have I got".
         */
        $studentProfile['academic_records'] = $qualifications['records'];

        $levelLabels = $this->proficiencyLabels();

        /*
         * Support both:
         * - legacy BIICFCareer recommendations
         * - current BiicfJobRole recommendations
         */
        $student->loadMissing([
            'currentRecommendations.career',
        ]);

        $recommended = $this->recommendedRolesInFull(
            $student->currentRecommendations
                ->pluck('biicf_job_role_id')
                ->filter()
                ->unique()
                ->values()
        );

        $recommendations = $student
            ->currentRecommendations
            ->sortBy('rank')
            ->values()
            ->map(function ($recommendation) use ($recommended, $levelLabels) {
                $legacyCareer =
                    $recommendation->career;

                $jobRole =
                    $recommended->get($recommendation->biicf_job_role_id);

                return [
                    'biicf_career_id' => $recommendation->biicf_career_id,

                    'biicf_job_role_id' => $recommendation->biicf_job_role_id,

                    'rank' => $recommendation->rank,

                    'match_score' => $recommendation->match_score,

                    'career_readiness_score' => $recommendation->career_readiness_score,

                    'matched_skills' => $recommendation->matched_skills ?? [],

                    'skill_gap_count' => count(
                        $recommendation->skill_gaps ?? []
                    ),

                    /*
                     * Every gap, not a sample. Discussing them
                     * is the adviser's main job, and showing it
                     * five while telling it there are nine
                     * meant it either named five as the whole
                     * set or knew it was missing some and could
                     * not say which.
                     */
                    'skill_gaps' => $recommendation->skill_gaps ?? [],

                    'development_plan' => $recommendation->development_plan ?? [],

                    'explanation' => $recommendation->explanation,

                    /*
                     * Legacy five-role recommendation details.
                     */
                    'career' => $legacyCareer
                        ? [
                            'job_title' => $legacyCareer->job_title,

                            'subsector' => $legacyCareer->subsector,

                            'technical_skills' => $this->normaliseArray(
                                $legacyCareer->technical_skills
                            ),

                            'soft_skills' => $this->normaliseArray(
                                $legacyCareer->soft_skills
                            ),

                            'entry_requirements' => $this->normaliseArray(
                                $legacyCareer->entry_requirements
                            ),

                            'recommended_training' => $this->normaliseArray(
                                $legacyCareer->recommended_training
                            ),

                            'certifications' => $this->normaliseArray(
                                $legacyCareer->certifications
                            ),

                            'job_description' => $legacyCareer->job_description,

                            'demand_level' => $legacyCareer->demand_level,
                        ]
                        : null,

                    /*
                     * The recommended role in full: what the
                     * student would actually have to meet.
                     */
                    'job_role' => $jobRole
                        ? $this->roleInFull($jobRole, $levelLabels)
                        : null,
                ];
            })
            ->all();

        $jobRoles = $this->compactRoles();

        $competencies = BiicfCompetency::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'type',
                'description',
            ])
            ->map(fn ($competency) => [
                'id' => $competency->id,

                'name' => $competency->name,

                'type' => $competency->type,

                'description' => $competency->description,
            ])
            ->all();

        return [
            'schema_version' => '1.1',

            /*
             * So a milestone's target date can be called overdue
             * or close. The model has no clock of its own.
             */
            'today' => now()->toDateString(),

            'student_profile' => $studentProfile,

            'student_qualification_summary' => $qualifications['summary'],

            'student_milestones' => $this->milestones($student),

            'career_recommendations' => $recommendations,

            'biicf_reference' => [
                'job_role_count' => count($jobRoles),

                'sub_sector_count' => BiicfSubSector::count(),

                'competency_count' => count($competencies),

                'bdqf' => $this->bdqf(),

                'proficiency_scales' => $this->proficiencyScales(),

                'job_levels' => BiicfJobLevel::query()
                    ->orderBy('sort_order')
                    ->pluck('name')
                    ->all(),

                'job_roles' => $jobRoles,

                'competencies' => $competencies,
            ],
        ];
    }

    /**
     * The student's qualifications, each with its BDQF level, and
     * where those leave them on the ladder.
     *
     * Entry requirements are written as BDQF levels, so without
     * these the adviser could read what a role asks for and still
     * not say whether the student meets it.
     *
     * @return array{records: array<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    private function qualifications(User $student): array
    {
        $records = $student->academicRecords()
            ->orderByDesc('is_programme_record')
            ->orderBy('start_date')
            ->get();

        $levelOf = fn (Collection $set): ?int => $set
            ->pluck('bdqf_level')
            ->filter(fn ($level) => $level !== null)
            ->max();

        return [
            'records' => $records
                ->map(fn (AcademicRecord $record) => [
                    'institution_name' => $record->institution_name,

                    'programme_name' => $record->programme_name,

                    'award' => $record->level,

                    /*
                     * Null where the student has not said which
                     * rung the award sits on. The adviser is told
                     * to call that unknown, not to guess.
                     */
                    'bdqf_level' => $record->bdqf_level,

                    'field_of_study' => $record->field_of_study,

                    'status' => $record->isAwarded()
                        ? 'awarded'
                        : 'still studying',

                    'is_their_politeknik_programme' => (bool) $record->is_programme_record,

                    'start_date' => $record->start_date?->toDateString(),

                    'end_date' => $record->end_date?->toDateString(),

                    'cgpa' => $record->cgpa !== null
                        ? (float) $record->cgpa
                        : null,

                    'subjects' => $this->normaliseArray($record->subjects),

                    'grades' => $this->normaliseArray($record->grades),

                    'achievements' => $record->achievements,
                ])
                ->values()
                ->all(),

            'summary' => [
                /*
                 * What a role can be entered with today. A
                 * qualification still being studied is not yet
                 * held, so it is counted separately rather than
                 * letting a first-year read as already qualified.
                 */
                'highest_awarded_bdqf_level' => $levelOf(
                    $records->filter(fn (AcademicRecord $record) => $record->isAwarded())
                ),

                'studying_towards_bdqf_level' => $levelOf(
                    $records->reject(fn (AcademicRecord $record) => $record->isAwarded())
                ),

                'qualifications_without_a_bdqf_level' => $records
                    ->whereNull('bdqf_level')
                    ->count(),
            ],
        ];
    }

    /**
     * The goals the student has set themselves.
     *
     * Open ones first, soonest first, so the ones the adviser is
     * most likely to be asked about lead.
     *
     * @return array<int, array<string, mixed>>
     */
    private function milestones(User $student): array
    {
        return $student->milestones()
            ->orderBy('is_completed')
            ->orderBy('target_date')
            ->get()
            ->map(fn (StudentMilestone $milestone) => [
                'title' => $milestone->title,

                'category' => $milestone->category,

                'description' => $milestone->description,

                'priority' => $milestone->priority,

                'target_date' => $milestone->target_date?->toDateString(),

                'is_completed' => (bool) $milestone->is_completed,

                'completed_date' => $milestone->completed_date?->toDateString(),

                'subtasks' => $this->normaliseArray($milestone->subtasks),

                'notes' => $milestone->notes,

                /*
                 * Whether proof has been uploaded, not the file.
                 * The adviser can say a milestone is evidenced
                 * without being handed a path it has no use for.
                 */
                'has_proof' => filled($milestone->proof_file_path),
            ])
            ->values()
            ->all();
    }

    /**
     * Annex 1: every BDQF level and what sits at it.
     *
     * @return array<string, mixed>
     */
    private function bdqf(): array
    {
        return [
            'name' => self::BDQF_NAME,

            'levels' => BiicfBdqfLevel::query()
                ->orderBy('level')
                ->get()
                ->map(fn (BiicfBdqfLevel $level) => [
                    'level' => $level->level,

                    'schools' => $level->schools_sector ?? [],

                    'technical_and_vocational' => $level->tvet_sector ?? [],

                    'higher_education' => $level->higher_education_sector ?? [],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Both proficiency scales, worded as the book words them.
     *
     * A required level is meaningless without them: Advanced is
     * the third of a soft skill's four rungs and Apply the third
     * of a technical competency's five, and they are not the same
     * requirement.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function proficiencyScales(): array
    {
        return BiicfProficiencyLevel::query()
            ->orderBy('scale')
            ->orderBy('level_number')
            ->get()
            ->groupBy('scale')
            ->map(fn (Collection $levels) => $levels
                ->map(fn (BiicfProficiencyLevel $level) => [
                    'level' => $level->level_number,

                    'name' => $level->name,

                    'description' => $level->description,
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * proficiency_level_id => how the level reads.
     *
     * @return array<int, array<string, mixed>>
     */
    private function proficiencyLabels(): array
    {
        return BiicfProficiencyLevel::all()
            ->mapWithKeys(fn (BiicfProficiencyLevel $level) => [
                $level->id => [
                    'name' => $level->name,

                    'level' => $level->level_number,

                    'out_of' => $level->scaleLength(),

                    'scale' => $level->scale,
                ],
            ])
            ->all();
    }

    /**
     * The recommended roles, with everything needed to describe
     * them in full.
     *
     * @param  Collection<int, int>  $roleIds
     * @return Collection<int, BiicfJobRole>
     */
    private function recommendedRolesInFull(Collection $roleIds): Collection
    {
        if ($roleIds->isEmpty()) {
            return collect();
        }

        return BiicfJobRole::query()
            ->whereKey($roleIds->all())
            ->with([
                'subSector:id,name',
                'descriptionPoints',
                'jobLevels:id,name,sort_order',
                'tiers.jobLevel:id,name',
                'tiers.entryRequirements',
                'tiers.competencies:id,name,type',
                'trainings:id,name',
                'programmingLanguages',
                'progressesTo:id,title',
                'progressesFrom:id,title',
            ])
            ->get()
            ->keyBy('id');
    }

    /**
     * One role as fully as the framework states it.
     *
     * @param  array<int, array<string, mixed>>  $levelLabels
     * @return array<string, mixed>
     */
    private function roleInFull(BiicfJobRole $role, array $levelLabels): array
    {
        return [
            'id' => $role->id,

            'title' => $role->title,

            'sub_sector' => $role->subSector?->name,

            'functional_group' => $role->functional_group,

            'job_description' => $this->descriptionOf($role),

            'career_path_level' => $role->career_path_level,

            'job_levels' => $role->jobLevels->pluck('name')->all(),

            /*
             * A column of the book's table per tier. The first is
             * the way in; the others are what the role asks for
             * as the holder rises within it.
             */
            'tiers' => $role->tiers
                ->sortBy('sort_order')
                ->values()
                ->map(fn (BiicfJobRoleTier $tier) => [
                    'label' => $tier->label,

                    'job_level' => $tier->jobLevel?->name,

                    'entry_requirements' => $tier->entryRequirements
                        ->sortBy('alternative_number')
                        ->values()
                        ->map(fn (BiicfEntryRequirement $requirement) => [
                            /*
                             * The book's own sentence, which says
                             * plainly what is wanted. Alternatives
                             * are separate rows and any one of
                             * them is enough.
                             */
                            'as_written' => $requirement->raw_text,

                            'bdqf_level' => $requirement->bdqf_level,

                            'field_of_study' => $requirement->field_of_study,

                            'years_experience' => $requirement->years_experience,

                            'requires_industry_certification' => (bool) $requirement->requires_industry_certification,

                            'requires_industry_experience' => (bool) $requirement->requires_industry_experience,
                        ])
                        ->all(),

                    'competencies' => $tier->competencies
                        ->map(fn ($competency) => [
                            'name' => $competency->name,

                            'type' => $competency->type,

                            'required_level' => $levelLabels[$competency->pivot->proficiency_level_id] ?? null,

                            'is_core' => (bool) $competency->pivot->is_core,
                        ])
                        ->values()
                        ->all(),
                ])
                ->all(),

            'trainings' => $role->trainings->pluck('name')->values()->all(),

            'programming_languages' => $role->programmingLanguages
                ->pluck('name')
                ->values()
                ->all(),

            'leads_to' => $role->progressesTo->pluck('title')->values()->all(),

            'comes_from' => $role->progressesFrom->pluck('title')->values()->all(),
        ];
    }

    /**
     * Every role in the framework, one compact line each.
     *
     * Enough to answer "what could I do with a level 5" or "what
     * does this lead to" for any role, without the full table of
     * every one riding along on every question.
     *
     * @return array<int, array<string, mixed>>
     */
    private function compactRoles(): array
    {
        return BiicfJobRole::query()
            ->with([
                'subSector:id,name',
                'descriptionPoints',
                'jobLevels:id,name,sort_order',
                'tiers.entryRequirements',
                'progressesTo:id,title',
                'progressesFrom:id,title',
            ])
            ->orderBy('title')
            ->get([
                'id',
                'sub_sector_id',
                'title',
                'job_description',
                'career_path_level',
            ])
            ->map(fn (BiicfJobRole $role) => [
                'id' => $role->id,

                'title' => $role->title,

                'sub_sector' => $role->subSector?->name,

                'job_description' => $this->descriptionOf($role),

                'career_path_level' => $role->career_path_level,

                'job_levels' => $role->jobLevels->pluck('name')->all(),

                /*
                 * The BDQF levels the role can be entered at, one
                 * per way in, lowest first — so the lowest is the
                 * least that gets a student through the door.
                 */
                'entry_bdqf_levels' => $role->tiers
                    ->sortBy('sort_order')
                    ->first()
                    ?->entryRequirements
                    ->pluck('bdqf_level')
                    ->filter(fn ($level) => $level !== null)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all() ?? [],

                'leads_to' => $role->progressesTo->pluck('title')->values()->all(),

                'comes_from' => $role->progressesFrom->pluck('title')->values()->all(),
            ])
            ->all();
    }

    /**
     * A role's description, from its rows rather than the old
     * column.
     *
     * The column is still written by the seeder but stops being
     * the truth once the reference book is imported, and the
     * adviser going quietly description-less would be hard to
     * spot. The recommended roles used to read the column even
     * though the catalogue beside them had moved on.
     */
    private function descriptionOf(BiicfJobRole $role): ?string
    {
        $fromRows = $role->descriptionPoints
            ->pluck('text')
            ->implode(' ');

        return $fromRows !== ''
            ? $fromRows
            : $role->job_description;
    }

    /**
     * Ensure legacy JSON fields are represented as arrays
     * in the Career Adviser context.
     */
    private function normaliseArray(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (
            is_string($value)
            && trim($value) !== ''
        ) {
            $decoded = json_decode(
                $value,
                true
            );

            if (is_array($decoded)) {
                return $decoded;
            }

            return [$value];
        }

        return [];
    }
}
