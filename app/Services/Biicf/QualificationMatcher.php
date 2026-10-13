<?php

namespace App\Services\Biicf;

use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRoleTier;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Whether a student's qualifications open a tier of a role.
 *
 * Nothing calls this at present. The explorer used to put its
 * verdict beside every entry requirement and no longer does: the
 * book's own sentence — "BDQF Level 2 in IT or related field
 * with relevant industry experience" — says plainly what is
 * wanted, and a student can read it without being told what it
 * means. Where the app's reading differed from the sentence, it
 * was the app that was wrong.
 *
 * Kept because the comparison itself is sound and tested, and a
 * page that matches roles to a profile is a thing this project
 * may yet want. It is not kept because anything needs it today.
 *
 * The framework states entry as a BDQF level and a field of
 * study, usually two ways in, and meeting any one of them is
 * enough. What it also asks for — years in the field, an
 * industry certification — the app holds no verified record of,
 * so those are reported as what the role wants rather than
 * turned into a verdict.
 *
 * Three outcomes, never two: met, not met, and undetermined for
 * an alternative with no level to compare, or a student whose
 * qualifications carry no level.
 */
class QualificationMatcher
{
    public const MET = 'met';

    public const NOT_MET = 'not_met';

    public const UNDETERMINED = 'undetermined';

    /**
     * @return array<string, mixed>
     */
    public function forTier(User $student, BiicfJobRoleTier $tier): array
    {
        $awarded = $this->highestLevel($student, awarded: true);
        $studying = $this->highestLevel($student, awarded: false);

        $alternatives = $tier->entryRequirements
            ->map(fn (BiicfEntryRequirement $requirement) => $this->check(
                $requirement,
                $awarded,
                $studying
            ))
            ->values();

        return [
            'held_level' => $awarded,
            'studying_level' => $studying,
            /*
             * The tier as a whole is open if any one way in is,
             * which is what OR means in the book. Undetermined
             * only when nothing was met and something could not
             * be judged — an alternative that cannot be checked
             * should not read as a refusal.
             */
            'outcome' => $this->overall($alternatives),
            'alternatives' => $alternatives,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function check(
        BiicfEntryRequirement $requirement,
        ?int $awarded,
        ?int $studying
    ): array {
        $outcome = match (true) {
            ! $requirement->isMeasurable() => self::UNDETERMINED,
            $awarded === null => self::UNDETERMINED,
            $awarded >= $requirement->bdqf_level => self::MET,
            default => self::NOT_MET,
        };

        return [
            'alternative_number' => $requirement->alternative_number,
            'text' => $requirement->raw_text,
            'bdqf_level' => $requirement->bdqf_level,
            'outcome' => $outcome,
            'conditions' => $requirement->conditions(),
            /*
             * Said separately from the outcome: a student part
             * way through a diploma has not met a requirement,
             * but "you will on finishing" is the useful half of
             * that answer.
             */
            'met_on_finishing' => $outcome === self::NOT_MET
                && $studying !== null
                && $requirement->bdqf_level !== null
                && $studying >= $requirement->bdqf_level,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $alternatives
     */
    private function overall(Collection $alternatives): string
    {
        if ($alternatives->isEmpty()) {
            return self::UNDETERMINED;
        }

        if ($alternatives->contains('outcome', self::MET)) {
            return self::MET;
        }

        if ($alternatives->contains('outcome', self::UNDETERMINED)) {
            return self::UNDETERMINED;
        }

        return self::NOT_MET;
    }

    /**
     * The highest rung the student's qualifications reach.
     *
     * Records carrying no level are passed over rather than
     * counted as zero: not knowing where a qualification sits is
     * not the same as it sitting at the bottom.
     */
    private function highestLevel(User $student, bool $awarded): ?int
    {
        return $student->academicRecords()
            ->when($awarded, fn ($query) => $query->awarded())
            ->when(! $awarded, fn ($query) => $query->inProgress())
            ->whereNotNull('bdqf_level')
            ->max('bdqf_level');
    }
}
