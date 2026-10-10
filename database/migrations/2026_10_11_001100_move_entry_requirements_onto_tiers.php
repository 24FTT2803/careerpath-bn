<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entry requirements belong to a tier, and there can be more
     * than one.
     *
     * The book states them per tier, not per role. IT Technician
     * carries three: BDQF 2 at Level 1, BDQF 3 at Level 2, and
     * at Level 3 either BDQF 4 or BDQF 3 with an industry
     * certification. One row per role could hold none of that.
     *
     * Two changes make a requirement comparable against a
     * student rather than merely printable:
     *
     *  - several numbered rows per tier, each being one of the
     *    book's OR alternatives;
     *  - a numeric BDQF level, since "BDQF Level 3" as prose
     *    cannot be measured against a qualification.
     *
     * The book's own sentence is kept in raw_text, because some
     * phrasings ("BDQF Level 2-4 by seniority") do not reduce to
     * one number. Where that happens the level stays null and a
     * match reports that it cannot be determined, which is
     * honest and better than inventing a number.
     *
     * Two deliberate choices about the schema itself:
     *
     * job_role_id stays. It is derivable from the tier, but
     * SQLite — which the tests run on — can neither drop a
     * column carrying a foreign key nor drop a constraint by
     * name, so a column with a key on it can never be removed
     * later. Keeping it also leaves the role's existing
     * relations working.
     *
     * job_role_tier_id is a plain indexed column with no
     * foreign key, for the same reason in reverse: this
     * migration has to be able to drop it again. Deleting a tier
     * cleans up its requirements through a model hook instead.
     */
    public function up(): void
    {
        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->unsignedBigInteger('job_role_tier_id')
                ->nullable()
                ->after('job_role_id')
                ->index();

            /*
             * Which of the tier's alternatives this is. Meeting
             * any one of them satisfies the tier.
             */
            $table->unsignedTinyInteger('alternative_number')->default(1);

            /*
             * The requirement as the book prints it, kept for
             * display and so an administrator can see what the
             * structured fields were read from.
             */
            $table->text('raw_text')->nullable();

            $table->boolean('requires_industry_certification')->default(false);
            $table->boolean('requires_industry_experience')->default(false);
        });

        $this->attachExistingRequirementsToTiers();

        /*
         * bdqf_level held a sentence about a level. It carries no
         * foreign key, so it can simply go and come back as the
         * level itself.
         */
        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->dropColumn('bdqf_level');
        });

        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->unsignedTinyInteger('bdqf_level')
                ->nullable()
                ->after('alternative_number');

            $table->unique(
                ['job_role_tier_id', 'alternative_number'],
                'entry_requirement_tier_alternative_unique'
            );
        });

        $this->readLevelsFromOriginalWording();
    }

    public function down(): void
    {
        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->dropUnique('entry_requirement_tier_alternative_unique');
            $table->dropColumn('bdqf_level');
        });

        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->string('bdqf_level')->nullable();
        });

        /*
         * Put the wording back where the sentence used to live.
         * Only the first alternative survives: the shape being
         * restored holds one requirement per role, which is the
         * very limitation this migration existed to remove.
         */
        foreach (
            DB::table('biicf_entry_requirements')
                ->select('id', 'raw_text')
                ->get() as $requirement
        ) {
            DB::table('biicf_entry_requirements')
                ->where('id', $requirement->id)
                ->update(['bdqf_level' => $requirement->raw_text]);
        }

        DB::table('biicf_entry_requirements')
            ->where('alternative_number', '>', 1)
            ->delete();

        Schema::table('biicf_entry_requirements', function (Blueprint $table) {
            $table->dropIndex(['job_role_tier_id']);

            $table->dropColumn([
                'job_role_tier_id',
                'alternative_number',
                'raw_text',
                'requires_industry_certification',
                'requires_industry_experience',
            ]);
        });
    }

    /**
     * Point each existing requirement at its role's tier.
     *
     * Done row by row rather than as an UPDATE with a JOIN,
     * which MySQL accepts and SQLite does not. There are only a
     * handful of rows.
     */
    private function attachExistingRequirementsToTiers(): void
    {
        /*
         * Every role has exactly one tier at this point, so
         * there is no ambiguity about which to pick.
         */
        $tierByRole = DB::table('biicf_job_role_tiers')
            ->orderBy('sort_order')
            ->pluck('id', 'job_role_id');

        foreach (
            DB::table('biicf_entry_requirements')
                ->select('id', 'job_role_id', 'bdqf_level')
                ->get() as $requirement
        ) {
            DB::table('biicf_entry_requirements')
                ->where('id', $requirement->id)
                ->update([
                    'job_role_tier_id' => $tierByRole[$requirement->job_role_id] ?? null,
                    'raw_text' => $requirement->bdqf_level,
                ]);
        }

        /*
         * A requirement whose role has no tier would be left
         * unattached. Silently stranding framework data is worse
         * than failing loudly.
         */
        $orphans = DB::table('biicf_entry_requirements')
            ->whereNull('job_role_tier_id')
            ->count();

        if ($orphans > 0) {
            throw new RuntimeException(
                "{$orphans} entry requirement(s) have no job role tier to attach to. "
                .'The tier backfill migration should have run first.'
            );
        }
    }

    /**
     * Read the BDQF level back out of the original wording,
     * where it names exactly one.
     *
     * A range such as "BDQF Level 2-4 (by seniority)" names
     * several and is left alone, so that a match says it cannot
     * be determined rather than picking the first number it saw.
     */
    private function readLevelsFromOriginalWording(): void
    {
        foreach (
            DB::table('biicf_entry_requirements')
                ->select('id', 'raw_text')
                ->whereNotNull('raw_text')
                ->get() as $requirement
        ) {
            $text = (string) $requirement->raw_text;

            if (preg_match('/level\s*\d\s*[-–]\s*\d/i', $text) === 1) {
                continue;
            }

            if (preg_match('/level\s*(\d)/i', $text, $matches) !== 1) {
                continue;
            }

            DB::table('biicf_entry_requirements')
                ->where('id', $requirement->id)
                ->update(['bdqf_level' => (int) $matches[1]]);
        }
    }
};
