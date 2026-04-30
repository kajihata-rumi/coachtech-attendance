<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_request_id');
            $table->foreignId('break_time_id')->nullable();
            $table->time('requested_break_start')->nullable();
            $table->time('requested_break_end')->nullable();
            $table->timestamps();

            $table->foreign('attendance_correction_request_id', 'bcr_attendance_req_fk')
                ->references('id')
                ->on('attendance_correction_requests')
                ->cascadeOnDelete();

            $table->foreign('break_time_id', 'bcr_break_time_fk')
                ->references('id')
                ->on('break_times')
                ->nullOnDelete();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_correction_requests');
    }
}
