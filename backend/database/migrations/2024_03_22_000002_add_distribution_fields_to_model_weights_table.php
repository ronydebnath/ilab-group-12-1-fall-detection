<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            // Distribution fields
            $table->string('distribution_status')->default('pending')->after('swarm_status')->comment('Status of model distribution');
            $table->json('distribution_results')->nullable()->after('distribution_status')->comment('Results of distribution attempts');
            $table->json('failed_nodes')->nullable()->after('distribution_results')->comment('Nodes where distribution failed');
            $table->timestamp('last_distribution_at')->nullable()->after('failed_nodes')->comment('Last distribution attempt');
            $table->string('distribution_method')->default('zmq')->after('last_distribution_at')->comment('Method used for distribution');
            $table->json('distribution_metadata')->nullable()->after('distribution_method')->comment('Additional distribution metadata');
            $table->string('distribution_checksum')->nullable()->after('distribution_metadata')->comment('Checksum of distributed model');
            $table->integer('distribution_retry_count')->default(0)->after('distribution_checksum')->comment('Number of distribution retries');
            $table->json('node_requirements')->nullable()->after('distribution_retry_count')->comment('Requirements for nodes to run this model');
            $table->json('distribution_history')->nullable()->after('node_requirements')->comment('History of distribution attempts');
        });
    }

    public function down()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            $table->dropColumn([
                'distribution_status',
                'distribution_results',
                'failed_nodes',
                'last_distribution_at',
                'distribution_method',
                'distribution_metadata',
                'distribution_checksum',
                'distribution_retry_count',
                'node_requirements',
                'distribution_history'
            ]);
        });
    }
}; 