<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id')->unique()->nullable()->after('id');
            $table->string('programme')->nullable()->after('email');
            $table->decimal('cgpa', 3, 2)->nullable()->after('programme');
            $table->string('role')->default('student')->after('cgpa');
            $table->string('avatar')->nullable()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'programme', 'cgpa', 'role', 'avatar', 'last_login_at']);
        });
    }
};