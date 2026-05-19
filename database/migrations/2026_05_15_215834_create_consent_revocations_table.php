<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consent_revocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('message_consent_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('channel'); // email|sms

            $table->string('purpose'); // transactional|marketing

            $table->string('reason');
            // stop|unsubscribe|bounce|complaint|manual|provider

            $table->timestamp('revoked_at');

            $table->string('source')->nullable();
            // twilio|resend|admin|system|webhook

            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['lead_id', 'channel', 'purpose']);

            $table->index(['channel', 'purpose']);

            $table->index('revoked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_revocations');
    }
};
