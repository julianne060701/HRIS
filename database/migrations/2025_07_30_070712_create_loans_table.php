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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('loan_type')->nullable(); 
            $table->decimal('original_amount', 10, 2);
            $table->decimal('balance', 10, 2); 
            $table->decimal('amortization_amount', 10, 2);
            $table->date('start_date')->nullable(); 
            $table->date('end_date')->nullable(); 
            $table->integer('numer_terms')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};