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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Holiday name, e.g. "New Year's Day"
            $table->date('date');           // Holiday date
            $table->string('type');         // Type, e.g. "Regular Holiday", "Special Non-Working Holiday"
            $table->string('description')->nullable(); // Optional description of the holiday
            $table->boolean('status')->default(true); // Status to indicate if the holiday is active
            $table->timestamps();         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
