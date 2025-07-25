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
        // Renaming from 'payrolldata' to 'payroll' for consistency
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id'); // Foreign key to employees table
            $table->date('payroll_start_date');
            $table->date('payroll_end_date');
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('basic_hours_pay', 10, 2)->nullable();
            $table->decimal('night_differential_pay', 10, 2)->nullable();
            $table->decimal('regular_holiday_pay', 10, 2)->nullable();
            $table->decimal('special_holiday_pay', 10, 2)->nullable();
            $table->decimal('overtime_pay', 10, 2)->nullable();
            $table->decimal('late_deduction', 10, 2)->nullable();
            $table->decimal('undertime_deduction', 10, 2)->nullable();
            $table->decimal('sss_contribution', 10, 2)->nullable();
            $table->decimal('philhealth_contribution', 10, 2)->nullable();
            $table->decimal('pagibig_contribution', 10, 2)->nullable();
            $table->decimal('tax_withheld', 10, 2)->nullable();
            $table->decimal('other_deductions', 10, 2)->nullable();
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_pay', 10, 2);
            $table->unsignedBigInteger('processed_by')->nullable(); // User who initiated the payroll run
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            // If you have a 'users' table and want to link who processed the payroll:
            // $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll'); // Drop 'payroll' table
    }
};