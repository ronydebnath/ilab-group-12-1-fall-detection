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
        // Drop existing foreign key on primary_carer_id
        Schema::table('elderly_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('elderly_profiles', 'primary_carer_id')) {
                $table->dropForeign(['primary_carer_id']);
            }
        });
        // Make primary_carer_id column nullable
        DB::statement('ALTER TABLE elderly_profiles ALTER COLUMN primary_carer_id DROP NOT NULL;');
        // Re-add foreign key constraint
        Schema::table('elderly_profiles', function (Blueprint $table) {
            $table->foreign('primary_carer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint
        Schema::table('elderly_profiles', function (Blueprint $table) {
            $table->dropForeign(['primary_carer_id']);
        });
        // Revert primary_carer_id column to not nullable
        DB::statement('ALTER TABLE elderly_profiles ALTER COLUMN primary_carer_id SET NOT NULL;');
        // Re-add original foreign key constraint
        Schema::table('elderly_profiles', function (Blueprint $table) {
            $table->foreign('primary_carer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
