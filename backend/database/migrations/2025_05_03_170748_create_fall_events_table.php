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
        Schema::create('fall_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elderly_id')->constrained('elderly')->onDelete('cascade');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->enum('status', ['detected', 'safe', 'alerted', 'resolved'])->default('detected');
            $table->json('sensor_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create table for fall event notifications
        Schema::create('fall_event_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fall_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('carer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['email', 'sms']);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fall_event_notifications');
        Schema::dropIfExists('fall_events');
    }
};
