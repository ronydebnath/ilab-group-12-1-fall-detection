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
        Schema::create('carer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->text('address');
            $table->string('qualification');
            $table->string('specialization')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->json('availability_schedule')->nullable();
            $table->integer('max_elderly_capacity')->default(5);
            $table->integer('current_elderly_count')->default(0);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamp('last_active_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carer_profiles');
    }
}; 