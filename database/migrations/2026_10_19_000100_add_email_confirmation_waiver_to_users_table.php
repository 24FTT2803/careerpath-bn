<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An account an administrator excused from confirming its email.
 *
 * Verification is asked of accounts created since the
 * requirement began, which is right for students who sign
 * themselves up and wrong for the ones an administrator types in
 * — a demonstration account, a stand-in for a student whose
 * address does not exist, anything created to be shown to
 * somebody. Those are asked to confirm a message nothing ever
 * sends, and there is no way past it.
 *
 * Kept apart from email_verified_at on purpose. That column
 * means the address was confirmed, and writing to it to get
 * somebody through the door would be recording something that
 * never happened: the address would then be trusted for
 * anything else that reads it. This records the other thing —
 * that confirmation was waived, by whom, and when — so the two
 * can never be mistaken for each other.
 *
 * Nullable, so every account that exists is unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_confirmation_waived_at')
                ->nullable()
                ->after('email_verified_at');

            $table->foreignId('email_confirmation_waived_by')
                ->nullable()
                ->after('email_confirmation_waived_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('email_confirmation_waived_by');
            $table->dropColumn('email_confirmation_waived_at');
        });
    }
};
