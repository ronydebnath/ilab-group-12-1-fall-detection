<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop the existing status check constraint
        DB::statement('ALTER TABLE fall_events DROP CONSTRAINT IF EXISTS fall_events_status_check');
        // Add new check constraint with updated statuses
        DB::statement("ALTER TABLE fall_events ADD CONSTRAINT fall_events_status_check CHECK (status IN ('detected','confirmed','false_alarm','resolved'))");
    }

    public function down(): void
    {
        // Revert to original statuses if rolling back
        DB::statement('ALTER TABLE fall_events DROP CONSTRAINT IF EXISTS fall_events_status_check');
        DB::statement("ALTER TABLE fall_events ADD CONSTRAINT fall_events_status_check CHECK (status IN ('detected','safe','alerted','resolved'))");
    }
}; 