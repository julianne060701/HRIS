<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedposts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');  // FK to employees.id
            $table->date('date');                       // Date of the schedule
            $table->string('shift');                    // Shift value: Morning, Night, etc.
            $table->timestamps();

            // Define foreign key constraint
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedposts');
    }
};
