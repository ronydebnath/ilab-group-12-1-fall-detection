<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing role check constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;');
        // Add updated check constraint including 'elderly'
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','carer','elderly'));"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop updated role check constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;');
        // Re-add original check constraint without 'elderly'
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin','carer'));"
        );
    }
};
