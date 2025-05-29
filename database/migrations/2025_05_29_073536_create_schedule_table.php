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
        Schema::create('schedule', function (Blueprint $table) {
            $table->id();
            $table->string('shift');
            $table->string('desc');
            $table->time('xptd_time_in')->nullable();
            $table->time('xptd_time_out')->nullable();
            $table->time('xptd_brk_in')->nullable();
            $table->time('xptd_brk_out')->nullable();
            $table->integer('wrkhrs')->nullable();
            $table->string('stat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
