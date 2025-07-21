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
        Schema::create('overtime', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->date('ot_date');
            $table->time('ot_in');
            $table->time('ot_out');
            $table->integer('total_ot_hours');
            $table->integer('is_approved');
            $table->float('approved_hours');
            $table->float('ot_reg_holiday_hours');
            $table->float('ot_spec_holiday_hours');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime');
    }
};
