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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birthday');
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
        
            // Government IDs
            $table->string('sss')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('tin')->nullable();
            $table->string('pagibig')->nullable();
        
            // Job Info
            $table->enum('status', ['Probationary', 'Regular', 'Resigned'])->default('Probationary');
            $table->string('department');
            $table->decimal('salary', 10, 2)->default(0.00);
        
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
