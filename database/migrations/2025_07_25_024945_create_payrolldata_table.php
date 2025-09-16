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
        Schema::create('payrolldata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->string('employee_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');

            $table->date('payroll_start_date');
            $table->date('payroll_end_date');
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('basic_hours_pay', 10, 2);
            
            // New pay fields
            $table->decimal('night_differential_pay', 10, 2)->default(0.00);
            $table->decimal('night_differential_pay_reg', 10, 2)->default(0.00);
            $table->decimal('night_differential_pay_spec', 10, 2)->default(0.00);
            $table->decimal('regular_holiday_pay', 10, 2)->default(0.00);
            $table->decimal('special_holiday_pay', 10, 2)->default(0.00);
            $table->decimal('overtime_pay', 10, 2)->default(0.00);
            $table->decimal('ot_reg_holiday_pay', 10, 2)->default(0.00);
            $table->decimal('ot_spec_holiday_pay', 10, 2)->default(0.00);
            $table->decimal('ot_night_diff_rdr_pay', 10, 2)->default(0.00);
            $table->decimal('rest_day_pay', 10, 2)->default(0.00);
            $table->decimal('rest_reg_pay', 10, 2)->default(0.00);
            $table->decimal('rest_spec_pay', 10, 2)->default(0.00);
            $table->decimal('rest_ot_pay', 10, 2)->default(0.00);

            // Deduction fields
            $table->decimal('late_deduction', 10, 2)->default(0.00);
            $table->decimal('undertime_deduction', 10, 2)->default(0.00);
            $table->decimal('sss_contribution', 10, 2)->default(0.00);
            $table->decimal('philhealth_contribution', 10, 2)->default(0.00);
            $table->decimal('pagibig_contribution', 10, 2)->default(0.00);
            $table->decimal('tax_withheld', 10, 2)->default(0.00);
            $table->decimal('loan_deduction', 10, 2)->default(0.00);
            $table->decimal('other_deductions', 10, 2)->default(0.00);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_pay', 10, 2);
            
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->unique(['payroll_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolldata');
    }
};