<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('target_month');
            $table->integer('total_working_minutes')->default(0);
            $table->integer('total_overtime_minutes')->default(0);
            $table->integer('average_working_minutes')->default(0);
            $table->integer('late_count')->default(0);
            $table->integer('early_leave_count')->default(0);
            $table->integer('long_work_count')->default(0);
            $table->unique(['user_id', 'target_month']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_summaries');
    }
}
