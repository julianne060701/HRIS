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
            Schema::create('employee_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id');
                $table->date('date');
                  $table->string('shift_code')->nullable();
                $table->unsignedBigInteger('leave_type_id')->nullable()->after('shift_code');
                $table->timestamps();

                // Foreign key constraint
                $table->foreign('leave_type_id')
                  ->references('id')
                  ->on('leave_types')
                  ->onDelete('set null');
                        });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('employee_schedules');
        }
    };
