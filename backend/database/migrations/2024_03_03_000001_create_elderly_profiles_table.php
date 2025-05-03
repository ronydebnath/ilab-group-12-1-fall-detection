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
        Schema::create('elderly_profiles', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('profile_photo')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // in cm
            $table->decimal('weight', 5, 2)->nullable(); // in kg
            $table->string('blood_type', 5)->nullable();
            $table->string('national_id')->nullable();
            
            // Contact Information
            $table->string('primary_phone');
            $table->string('secondary_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('current_address');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            
            // Health Information
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('disabilities')->nullable();
            $table->enum('mobility_status', ['independent', 'needs_assistance', 'wheelchair_bound']);
            $table->enum('vision_status', ['normal', 'glasses', 'impaired']);
            $table->enum('hearing_status', ['normal', 'hearing_aid', 'impaired']);
            $table->date('last_medical_checkup')->nullable();
            
            // Care Information
            $table->foreignId('primary_carer_id')->constrained('users');
            $table->foreignId('secondary_carer_id')->nullable()->constrained('users');
            $table->enum('care_level', ['basic', 'moderate', 'intensive']);
            $table->text('special_care_instructions')->nullable();
            $table->text('daily_routine_notes')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->string('preferred_language', 50)->default('English');
            
            // Device Information
            $table->string('device_id')->nullable();
            $table->enum('device_status', ['active', 'inactive'])->default('inactive');
            $table->date('last_device_check')->nullable();
            $table->integer('device_battery_level')->nullable();
            $table->string('device_location')->nullable();
            
            // Additional Information
            $table->string('preferred_hospital')->nullable();
            $table->text('insurance_information')->nullable();
            $table->enum('living_situation', ['lives_alone', 'with_family', 'assisted_living']);
            $table->enum('activity_level', ['active', 'moderate', 'sedentary']);
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
        Schema::dropIfExists('elderly_profiles');
    }
}; 