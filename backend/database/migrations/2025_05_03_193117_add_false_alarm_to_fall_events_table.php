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
            $table->boolean('false_alarm')->default(false)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            $table->dropColumn('false_alarm');
        });
    }
};
