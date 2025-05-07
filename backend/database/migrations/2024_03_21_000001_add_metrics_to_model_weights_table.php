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
        Schema::table('model_weights', function (Blueprint $table) {
            $table->float('accuracy')->nullable()->after('weights');
            $table->float('precision')->nullable()->after('accuracy');
            $table->float('recall')->nullable()->after('precision');
            $table->float('f1_score')->nullable()->after('recall');
            $table->json('metadata')->nullable()->after('f1_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_weights', function (Blueprint $table) {
            $table->dropColumn([
                'accuracy',
                'precision',
                'recall',
                'f1_score',
                'metadata'
            ]);
        });
    }
}; 