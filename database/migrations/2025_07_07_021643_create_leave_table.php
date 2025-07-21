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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->date('date_start');
            $table->date('date_end');
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('total_days');
            $table->string('status')->default('pending');

            $table->text('reason')->nullable();
            $table->text('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            
            $table->timestamps();

            // Foreign key constraint if you have an 'employees' table
            // $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            // If 'leave_type' should strictly refer to the 'name' column in 'leave_types'
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};