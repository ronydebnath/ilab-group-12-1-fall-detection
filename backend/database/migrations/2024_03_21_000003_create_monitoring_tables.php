<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('component');
            $table->string('status');
            $table->json('metrics')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['component', 'status']);
            $table->index('checked_at');
        });

        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->float('value');
            $table->string('unit');
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['metric_name', 'recorded_at']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('severity');
            $table->string('status')->default('pending');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'severity', 'status']);
            $table->index('triggered_at');
        });

        Schema::create('alert_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained()->onDelete('cascade');
            $table->string('channel');
            $table->string('recipient');
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_notifications');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('system_health_checks');
    }
}; 