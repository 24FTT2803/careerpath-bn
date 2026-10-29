<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sign-ups waiting for their owner to authenticate the address.
 *
 * Until now signing up created the account at once and asked for
 * the address to be confirmed afterwards, so anyone could open an
 * account in a Politeknik student's name. The account is now
 * created only when the link sent to that address is used, and
 * until then the details wait here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('programme');
            $table->string('password');
            $table->string('student_id')->nullable();
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
