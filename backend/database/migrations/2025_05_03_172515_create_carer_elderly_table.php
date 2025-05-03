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
        // Create pivot table for elderly-carer relationships
        Schema::create('carer_elderly', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carer_id')->constrained()->onDelete('cascade');
            $table->foreignId('elderly_id')->constrained('elderly')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carer_elderly');
    }
};
