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
        Schema::create('schedposts', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('schedule');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedposts');
    }
};
