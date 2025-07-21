<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('dtr', function (Blueprint $table) {
        $table->id();
        $table->string('employee_id');
        $table->string('shift_code')->nullable();
        $table->date('transindate');
        $table->time('time_in');
        $table->time('xptd_time_in')->nullable();
        $table->date('transoutdate');
        $table->time('time_out');
        $table->time('xptd_time_out')->nullable();
        $table->integer('is_late');
        $table->integer('late_minutes');
        $table->integer('is_undertime');
        $table->integer('undertime_minutes');
        $table->decimal('total_hours', 8, 2)->nullable(); 
        $table->decimal('night_diff', 8, 2)->nullable();
        $table->decimal('night_diff_reg', 8, 2)->nullable();
        $table->decimal('night_diff_spec', 8, 2)->nullable();
        $table->decimal('reg_holiday_hours', 8, 2)->nullable();
        $table->decimal('spec_holiday_hours', 8, 2)->nullable();
        $table->unsignedBigInteger('leave_type_id')->nullable();    
        $table->timestamps();
    });

    
    
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtr');
    }
};
