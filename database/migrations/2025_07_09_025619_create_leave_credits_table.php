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
        Schema::create('leave_credits', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id'); // Corrected from sti to string
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('all_leave');
            $table->integer('rem_leave');
            $table->timestamps();

            // This foreign key references the 'employee_id' column in the 'employees' table
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');

            // This foreign key references the 'id' column in the 'leave_types' table
            $table->foreign('leave_type_id')
                  ->references('id')
                  ->on('leave_types')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credits');
    }
};
