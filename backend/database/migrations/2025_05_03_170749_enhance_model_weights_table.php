<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            $table->string('encryption_key_id')->nullable()->after('id');
            $table->string('encryption_algorithm')->default('AES-256-GCM')->after('encryption_key_id');
            $table->string('encryption_iv')->nullable()->after('encryption_algorithm');
            
            // Training Metadata
            $table->json('training_metadata')->nullable()->after('metadata');
            $table->integer('training_epochs')->nullable()->after('training_metadata');
            $table->float('learning_rate')->nullable()->after('training_epochs');
            $table->string('optimizer')->nullable()->after('learning_rate');
            $table->json('hyperparameters')->nullable()->after('optimizer');
            
            // Client Information
            $table->string('client_version')->nullable()->after('version');
            $table->string('client_platform')->nullable()->after('client_version');
            $table->string('client_device_id')->nullable()->after('client_platform');
            $table->string('client_os_version')->nullable()->after('client_device_id');
            
            // Performance Metrics
            $table->json('performance_metrics')->nullable()->after('f1_score');
            $table->float('inference_time')->nullable()->after('performance_metrics');
            $table->float('memory_usage')->nullable()->after('inference_time');
            $table->float('battery_impact')->nullable()->after('memory_usage');
            
            // Swarm Learning Specific
            $table->string('aggregation_method')->default('fedavg')->after('aggregation_status');
            $table->integer('round_number')->default(0)->after('aggregation_method');
            $table->json('participating_nodes')->nullable()->after('round_number');
            $table->float('aggregation_confidence')->nullable()->after('participating_nodes');
            
            // Model Architecture
            $table->json('model_architecture')->nullable()->after('model_type');
            $table->integer('input_shape')->nullable()->after('model_architecture');
            $table->integer('output_shape')->nullable()->after('input_shape');
            $table->json('layer_configurations')->nullable()->after('output_shape');
            
            // Validation and Quality
            $table->float('validation_accuracy')->nullable()->after('accuracy');
            $table->float('validation_loss')->nullable()->after('validation_accuracy');
            $table->json('validation_metrics')->nullable()->after('validation_loss');
            $table->boolean('quality_check_passed')->default(false)->after('validation_metrics');
            
            // Monitoring and Maintenance
            $table->timestamp('last_sync_at')->nullable()->after('updated_at');
            $table->timestamp('last_validation_at')->nullable()->after('last_sync_at');
            $table->string('sync_status')->default('pending')->after('last_validation_at');
            $table->json('error_logs')->nullable()->after('sync_status');
            
            // Add indexes for frequently queried columns
            $table->index('version');
            $table->index('client_version');
            $table->index('is_active');
            $table->index('model_ready');
            $table->index('aggregation_status');
            $table->index('sync_status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('model_weights', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['version']);
            $table->dropIndex(['client_version']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['model_ready']);
            $table->dropIndex(['aggregation_status']);
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['created_at']);
            
            // Remove columns
            $table->dropColumn([
                'encryption_key_id',
                'encryption_algorithm',
                'encryption_iv',
                'training_metadata',
                'training_epochs',
                'learning_rate',
                'optimizer',
                'hyperparameters',
                'client_version',
                'client_platform',
                'client_device_id',
                'client_os_version',
                'performance_metrics',
                'inference_time',
                'memory_usage',
                'battery_impact',
                'aggregation_method',
                'round_number',
                'participating_nodes',
                'aggregation_confidence',
                'model_architecture',
                'input_shape',
                'output_shape',
                'layer_configurations',
                'validation_accuracy',
                'validation_loss',
                'validation_metrics',
                'quality_check_passed',
                'last_sync_at',
                'last_validation_at',
                'sync_status',
                'error_logs'
            ]);
        });
    }
};
