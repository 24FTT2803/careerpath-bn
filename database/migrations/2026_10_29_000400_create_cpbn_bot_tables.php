<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The CPBN Bot: CareerPath BN's guide to the website itself.
 *
 * It is a separate assistant from the Career Adviser, with its
 * own conversations, so the two never share a thread.
 *
 * Its allowance is measured in AI tokens, so usage can now be
 * recorded as an amount rather than only as one use. Every
 * earlier usage row was one use, which is what the default of 1
 * says.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_usages', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('feature_key');
        });

        Schema::create('cpbn_bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_message_at']);
        });

        Schema::create('cpbn_bot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpbn_bot_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->text('content');

            /* Pages the reply pointed to, as page keys. */
            $table->json('links')->nullable();

            /* What the reply cost, on the assistant's message. */
            $table->unsignedInteger('tokens')->nullable();
            $table->timestamps();

            $table->index(['cpbn_bot_conversation_id', 'id']);
        });

        /*
         * The bot's features are added here as well as in the
         * seeders so an installation that is already running gets
         * them. A fresh database has no plans yet; the seeders add
         * them there.
         */
        if (! DB::table('plans')->exists()) {
            return;
        }

        $now = now();

        DB::table('feature_definitions')->insertOrIgnore([
            [
                'key' => 'cpbn_bot.enabled',
                'name' => 'CPBN Bot',
                'category' => 'CPBN Bot',
                'value_type' => 'boolean',
                'parent_key' => null,
                'global_enabled' => true,
                'sort_order' => 250,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cpbn_bot.token_quota',
                'name' => 'CPBN Bot Token Limit',
                'category' => 'CPBN Bot',
                'value_type' => 'quota',
                'parent_key' => 'cpbn_bot.enabled',
                'global_enabled' => true,
                'sort_order' => 260,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cpbn_bot.history.enabled',
                'name' => 'CPBN Bot History',
                'category' => 'History',
                'value_type' => 'boolean',
                'parent_key' => 'cpbn_bot.enabled',
                'global_enabled' => true,
                'sort_order' => 320,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $values = [
            'free' => [
                'cpbn_bot.enabled' => true,
                'cpbn_bot.token_quota' => ['mode' => 'recurring', 'amount' => 50000, 'period_value' => 1, 'period_unit' => 'day'],
                'cpbn_bot.history.enabled' => false,
            ],
            'premium' => [
                'cpbn_bot.enabled' => true,
                'cpbn_bot.token_quota' => ['mode' => 'recurring', 'amount' => 200000, 'period_value' => 1, 'period_unit' => 'day'],
                'cpbn_bot.history.enabled' => true,
            ],
        ];

        foreach (DB::table('plans')->get(['id', 'code']) as $plan) {
            foreach ($values[$plan->code] ?? $values['free'] as $key => $value) {
                DB::table('plan_features')->insertOrIgnore([
                    'plan_id' => $plan->id,
                    'key' => $key,
                    'value' => json_encode($value),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $keys = ['cpbn_bot.enabled', 'cpbn_bot.token_quota', 'cpbn_bot.history.enabled'];

        DB::table('plan_features')->whereIn('key', $keys)->delete();
        DB::table('feature_definitions')->whereIn('key', $keys)->delete();
        DB::table('feature_usages')->where('feature_key', 'like', 'cpbn_bot.%')->delete();

        Schema::dropIfExists('cpbn_bot_messages');
        Schema::dropIfExists('cpbn_bot_conversations');

        Schema::table('feature_usages', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
