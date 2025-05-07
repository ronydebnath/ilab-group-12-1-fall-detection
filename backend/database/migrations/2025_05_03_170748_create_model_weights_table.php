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
        Schema::create('model_weights', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('model_type');
            $table->json('weights');
            $table->text('description')->nullable();
            $table->float('accuracy')->nullable();
            $table->float('precision')->nullable();
            $table->float('recall')->nullable();
            $table->float('f1_score')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('total_layers')->default(0);
            $table->integer('total_parameters')->default(0);
            $table->boolean('model_ready')->default(false);
            $table->string('node_id')->nullable();
            $table->string('aggregation_status')->default('pending');
            $table->integer('peers_connected')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_weights');
    }
};
