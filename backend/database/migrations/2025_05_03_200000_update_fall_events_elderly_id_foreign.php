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
        Schema::table('fall_events', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['elderly_id']);

            // Recreate the foreign key to reference elderly_profiles
            $table->foreign('elderly_id')
                  ->references('id')
                  ->on('elderly_profiles')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            // Drop the updated foreign key constraint
            $table->dropForeign(['elderly_id']);

            // Restore the original foreign key to reference elderly
            $table->foreign('elderly_id')
                  ->references('id')
                  ->on('elderly')
                  ->onDelete('cascade');
        });
    }
}; 