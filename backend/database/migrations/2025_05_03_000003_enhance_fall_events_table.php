<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            $table->json('notification_channels')->nullable()->after('response_actions');
            $table->json('context')->nullable()->after('notification_channels');
        });
    }

    public function down(): void
    {
        Schema::table('fall_events', function (Blueprint $table) {
            $table->dropColumn(['notification_channels', 'context']);
        });
    }
}; 