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
        Schema::create('employee_deduction', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('payroll_id');
            $table->decimal('monthly_salary');
            $table->decimal('sss_employee_contribution');
            $table->decimal('sss_employer_contribution');
            $table->decimal('pagibig_employee_contribution');
            $table->decimal('pagibig_employer_contribution');
            $table->decimal('philhealth_employee_contribution');
            $table->decimal('philhealth_employer_contribution');
            $table->decimal('withholdingtax');
            $table->decimal('total_employee_deduction');
            $table->decimal('total_employer_deduction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_deduction');
    }
};
