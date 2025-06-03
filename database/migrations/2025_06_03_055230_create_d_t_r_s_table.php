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
        $table->date('transdate');
        $table->string('time_in');
        $table->string('time_out');
        $table->timestamps();
    });

    // Assuming you want to update an existing table (for example: `shifts` table or whatever table holds the data `shift`, `desc`, etc.)
    Schema::table($schedule, function (Blueprint $table) {
        // Add the necessary fields to the existing table
        $table->string('shift')->nullable();
        $table->string('desc')->nullable();
        $table->string('xptd_time_in')->nullable();
        $table->string('xptd_time_out')->nullable();
        $table->string('xptd_brk_in')->nullable();
        $table->string('xptd_brk_out')->nullable();
        $table->string('wrkhrs')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtr');

        // Rolling back changes to the existing table
        Schema::table('existing_table_name', function (Blueprint $table) {
            $table->dropColumn(['shift', 'desc', 'xptd_time_in', 'xptd_time_out', 'xptd_brk_in', 'xptd_brk_out', 'wrkhrs']);
        });
    }
};
