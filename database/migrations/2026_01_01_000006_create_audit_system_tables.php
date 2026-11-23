<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotificationChannels\WebPush\PushSubscription;

return new class extends Migration {
    /**
     * Run the migrations.
     * System Settings & Infrastructure:
     * Print Templates, Document Counters, and Security Logs.
     */
    public function up(): void
    {
        // 1. Document Numbering Counters (Sequential number generator)
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->integer('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });

        // 3. Security Logs (Threat monitoring, auth events, access hardening)
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 100);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at'], 'idx_security_logs_event_created');
            $table->index(['user_id', 'created_at'], 'idx_security_logs_user_created');
            $table->index(['ip_address', 'created_at'], 'idx_security_logs_ip_created');
        });

        // 4. Web Push Subscriptions (Browser push notification tokens)
        $connection = config('webpush.database_connection');
        $tableName = config('webpush.table_name', 'push_subscriptions');

        if (!Schema::connection($connection)->hasTable($tableName)) {
            Schema::connection($connection)->create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
                $table->string('endpoint', PushSubscription::ENDPOINT_MAX_LENGTH)
                    ->charset('ascii')
                    ->unique();
                $table->string('public_key')->nullable();
                $table->string('auth_token')->nullable();
                $table->string('content_encoding')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('webpush.database_connection');
        $tableName = config('webpush.table_name', 'push_subscriptions');
        Schema::connection($connection)->dropIfExists($tableName);

        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('document_counters');
    }
};
