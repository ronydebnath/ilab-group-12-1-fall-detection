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
            $table->foreignId('elderly_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('detected_at');
            $table->decimal('confidence_score', 5, 2)->nullable(); // Detection confidence 0-100%
            $table->json('location')->nullable(); // GPS coordinates JSON
            $table->json('sensor_data')->nullable(); // JSON string of sensor readings
            $table->enum('status', ['detected', 'confirmed', 'false_alarm', 'safe', 'alerted', 'resolved'])->default('detected');
            $table->text('notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Indexes for faster queries
            $table->index(['elderly_id', 'detected_at']);
            $table->index('status');
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
