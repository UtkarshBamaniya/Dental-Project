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
        Schema::create('dental_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id');
            $table->string('appointment_no')->nullable()->unique();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->integer('doctor_id')->nullable();
            $table->enum('visit_type', ['First Visit', 'Follow-up'])->default('First Visit');
            $table->foreignId('appointment_type_id')->nullable()->constrained('appointment_types')->nullOnDelete();
            $table->text('chief_complaint')->nullable();
            $table->string('problem_area')->nullable();
            $table->string('tooth_no')->nullable();
            $table->enum('priority', ['Normal', 'Urgent', 'Emergency'])->default('Normal');
            $table->enum('status', ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled', 'No Show'])->default('Scheduled');
            $table->text('notes')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_appointments');
    }
};
