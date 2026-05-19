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
        Schema::create('message_consents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('webinar_registration_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('channel'); // sms|email
            $table->string('purpose'); // transactional|marketing

            $table->timestamp('consented_at');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->string('source')->nullable();

            $table->timestamps();

            $table->unique([
                'lead_id',
                'channel',
                'purpose',
            ]);

            $table->index(['channel', 'purpose']);
            $table->index('consented_at');
            $table->index('webinar_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_consents');
    }
};
