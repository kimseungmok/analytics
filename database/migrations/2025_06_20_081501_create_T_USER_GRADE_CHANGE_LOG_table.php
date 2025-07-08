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
        Schema::create('T_USER_GRADE_CHANGE_LOG', function (Blueprint $table) {
            $table->bigIncrements('LOG_ID');
            $table->integer('TICKET');
            $table->date('SNAPSHOT_DATE');
            $table->date('PREV_SNAPSHOT_DATE')->nullable();
            $table->integer('SEGMENT_BEFORE_ID')->nullable()->index('idx_seg_before_id');
            $table->integer('SEGMENT_AFTER_ID')->nullable()->index('idx_seg_after_id');
            $table->string('CHANGE_TYPE', 10)->nullable();
            $table->string('COMMENT', 100)->nullable();
            $table->dateTime('CREATED_AT')->useCurrent();

            $table->index(['CHANGE_TYPE', 'SNAPSHOT_DATE'], 'idx_change_type_snapshot_date');
            $table->index(['SNAPSHOT_DATE', 'SEGMENT_BEFORE_ID', 'SEGMENT_AFTER_ID'], 'idx_date_seg_before_after');
            $table->primary(['LOG_ID'], 'pk__t_user_g__4364c882fc24cc2f');
            $table->unique(['TICKET', 'SNAPSHOT_DATE'], 'uq_ticket_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_USER_GRADE_CHANGE_LOG');
    }
};
