<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            // Swarm Learning specific fields
            $table->string('swarm_id')->nullable()->after('sync_status')->comment('Unique identifier for the swarm this model belongs to');
            $table->integer('swarm_round')->default(1)->after('swarm_id')->comment('Current round number in the swarm learning process');
            $table->json('swarm_metrics')->nullable()->after('swarm_round')->comment('Metrics specific to swarm learning performance');
            $table->json('swarm_contributors')->nullable()->after('swarm_metrics')->comment('List of nodes that contributed to this model');
            $table->float('swarm_contribution_score')->nullable()->after('swarm_contributors')->comment('Score indicating the quality of this node\'s contribution');
            $table->json('swarm_hyperparameters')->nullable()->after('swarm_contribution_score')->comment('Hyperparameters used in swarm learning');
            $table->string('swarm_aggregation_strategy')->default('fedavg')->after('swarm_hyperparameters')->comment('Strategy used for weight aggregation');
            $table->float('swarm_convergence_score')->nullable()->after('swarm_aggregation_strategy')->comment('Score indicating how well the model has converged');
            $table->json('swarm_validation_results')->nullable()->after('swarm_convergence_score')->comment('Validation results from swarm learning');
            $table->timestamp('swarm_last_updated')->nullable()->after('swarm_validation_results')->comment('Last time the model was updated by swarm learning');
            $table->string('swarm_status')->default('active')->after('swarm_last_updated')->comment('Current status in the swarm learning process');
        });
    }

    public function down()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            $table->dropColumn([
                'swarm_id',
                'swarm_round',
                'swarm_metrics',
                'swarm_contributors',
                'swarm_contribution_score',
                'swarm_hyperparameters',
                'swarm_aggregation_strategy',
                'swarm_convergence_score',
                'swarm_validation_results',
                'swarm_last_updated',
                'swarm_status'
            ]);
        });
    }
}; 