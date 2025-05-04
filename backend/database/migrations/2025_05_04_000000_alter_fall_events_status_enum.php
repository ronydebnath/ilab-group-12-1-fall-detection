<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old check constraint on status
        DB::statement("ALTER TABLE fall_events DROP CONSTRAINT IF EXISTS fall_events_status_check;");
        
        // Recreate the check constraint with the new 'false_alarm' value
        DB::statement("ALTER TABLE fall_events ADD CONSTRAINT fall_events_status_check CHECK (status IN ('detected', 'safe', 'alerted', 'resolved', 'false_alarm'));" );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the updated check constraint
        DB::statement("ALTER TABLE fall_events DROP CONSTRAINT IF EXISTS fall_events_status_check;");
        
        // Restore the original check constraint without 'false_alarm'
        DB::statement("ALTER TABLE fall_events ADD CONSTRAINT fall_events_status_check CHECK (status IN ('detected', 'safe', 'alerted', 'resolved'));" );
    }
}; 