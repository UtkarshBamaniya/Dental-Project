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
        Schema::create('dental_patient_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id');
            $table->string('blood_group')->nullable();
            $table->boolean('diabetes')->default(false);
            $table->boolean('blood_pressure')->default(false);
            $table->boolean('heart_disease')->default(false);
            $table->boolean('allergy')->default(false);
            $table->text('allergy_details')->nullable();
            $table->text('current_medicine')->nullable();
            $table->text('previous_dental_treatment')->nullable();
            $table->boolean('pregnancy_status')->default(false);
            $table->text('other_medical_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_patient_medical_histories');
    }
};
