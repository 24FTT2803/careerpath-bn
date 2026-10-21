<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where each piece of framework data came from, and whether
     * a later edition dropped it.
     *
     * Administrators will keep adding and editing framework data
     * by hand, exactly as they do now, while edition imports
     * arrive alongside. Without knowing which is which, an import
     * cannot behave correctly: a role an administrator added
     * deliberately would be offered for retirement merely because
     * the new book does not mention it, and a field someone
     * corrected by hand would be silently overwritten.
     *
     * retired_in_edition_id is how a dropped role leaves the
     * framework. Deleting it would cascade through
     * career_recommendations and take students' saved reports
     * with it, so nothing is ever deleted by an import — it is
     * marked as no longer current and stays readable.
     */
    private const TABLES = [
        'biicf_sub_sectors',
        'biicf_job_roles',
        'biicf_competencies',
        'biicf_trainings',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                /*
                 * Defaults to manual because that is what a row
                 * created through the admin pages is, and those
                 * pages are unchanged.
                 *
                 * A string rather than an enum: adding an enum to
                 * an existing table behaves differently across
                 * MySQL and SQLite, and the two values are
                 * enforced by the model instead.
                 */
                $table->string('origin', 20)->default('manual');

                $table->foreignId('source_edition_id')
                    ->nullable()
                    ->constrained('biicf_editions')
                    ->nullOnDelete();

                $table->unsignedSmallInteger('source_page')->nullable();

                /*
                 * Set once a later edition drops this entry. The
                 * row stays; it simply stops being part of the
                 * current framework.
                 */
                $table->foreignId('retired_in_edition_id')
                    ->nullable()
                    ->constrained('biicf_editions')
                    ->nullOnDelete();

                /*
                 * An imported row that someone has since edited
                 * by hand. The next import raises that as a
                 * conflict to resolve rather than choosing for
                 * them.
                 */
                $table->timestamp('hand_edited_at')->nullable();

                $table->foreignId('hand_edited_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });

            /*
             * What is already in these tables is framework
             * content, approximated from the book by a seeder
             * rather than typed in by an administrator, so it is
             * treated as imported. That matters when the first
             * real edition lands: this data is meant to be
             * superseded by it, not defended from it.
             */
            DB::table($name)->update(['origin' => 'imported']);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('source_edition_id');
                $table->dropConstrainedForeignId('retired_in_edition_id');
                $table->dropConstrainedForeignId('hand_edited_by');
                $table->dropColumn(['origin', 'source_page', 'hand_edited_at']);
            });
        }
    }
};
