<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            
            // Add fields for better analytics and reporting
            $table->string('detection_method')->nullable()->after('confidence_score'); // e.g., 'sensor', 'manual', 'ai'
            $table->string('location_description')->nullable()->after('location'); // Human-readable location
            $table->integer('response_time_seconds')->nullable()->after('resolved_at'); // Time taken to respond
            $table->json('response_actions')->nullable()->after('response_time_seconds'); // Actions taken during response
            $table->string('severity_level')->nullable()->after('status'); // e.g., 'low', 'medium', 'high'
            $table->json('medical_notes')->nullable()->after('notes'); // Medical assessment if any
            $table->boolean('required_medical_attention')->nullable()->after('severity_level');
            
            // Add indexes for common queries
            $table->index('detection_method');
            $table->index('severity_level');
            $table->index('required_medical_attention');
        });
    }

    public function down(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            // Drop indexes safely
            if (Schema::hasIndex('fall_events', 'fall_events_detection_method_index')) {
                $table->dropIndex('fall_events_detection_method_index');
            }
            if (Schema::hasIndex('fall_events', 'fall_events_severity_level_index')) {
                $table->dropIndex('fall_events_severity_level_index');
            }
            if (Schema::hasIndex('fall_events', 'fall_events_required_medical_attention_index')) {
                $table->dropIndex('fall_events_required_medical_attention_index');
            }

            // Drop columns
            $table->dropColumn([
                'detection_method',
                'location_description',
                'response_time_seconds',
                'response_actions',
                'severity_level',
                'medical_notes',
                'required_medical_attention'
            ]);
        });
    }
}; 