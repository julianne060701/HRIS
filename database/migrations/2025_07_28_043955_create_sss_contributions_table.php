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
         Schema::create('sss_contributions', function (Blueprint $table) {
            $table->id();
            $table->decimal('salary_range_from');
            $table->decimal('salary_range_to');
            $table->decimal('reg_ee_share');
            $table->decimal('reg_er_share');
            $table->decimal('ec_er_share')->default(0); 
            $table->decimal('wisp_ee_share')->default(0); 
            $table->decimal('wisp_er_share')->default(0); 
            $table->decimal('total_contribution')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sss_contributions');
    }
};
