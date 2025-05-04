<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fall_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elderly_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('detected_at');
            $table->decimal('confidence_score', 5, 2)->nullable(); // Detection confidence 0-100%
            $table->point('location')->nullable(); // GPS coordinates if available
            $table->string('sensor_data')->nullable(); // JSON string of sensor readings
            $table->enum('status', ['detected', 'confirmed', 'false_alarm', 'resolved'])->default('detected');
            $table->text('notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['elderly_id', 'detected_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fall_events');
    }
}; 