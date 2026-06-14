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
        Schema::create('dental_appointment_billings', function (Blueprint $table) {
            $table->id();
            $table->integer('appointment_id');
            $table->decimal('consultation_fee', 13, 2)->default(0);
            $table->decimal('treatment_estimate', 13, 2)->default(0);
            $table->decimal('discount', 13, 2)->default(0);
            $table->decimal('paid_amount', 13, 2)->default(0);
            $table->enum('payment_mode', ['Cash', 'UPI', 'Card', 'Bank Transfer'])->nullable();
            $table->enum('payment_status', ['Pending', 'Partial', 'Paid'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_appointment_billings');
    }
};
