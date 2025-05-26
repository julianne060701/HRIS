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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_code')->nullable(); // e.g., code to identify payroll batch
            $table->string('title')->nullable(); // e.g., Monthly Payroll, April Payroll
            $table->date('from_date');
            $table->date('to_date');
            $table->string('status'); // Present, Absent, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
