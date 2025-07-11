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
        Schema::create('T_GRADE_SNAPSHOT', function (Blueprint $table) {
            $table->bigIncrements('SNAPSHOT_ID');
            $table->integer('TICKET');
            $table->date('SNAPSHOT_DATE');
            $table->integer('SEGMENT_ID')->nullable();
            $table->integer('LAST_VISTED_SHOP')->nullable()->index('ix_t_grade_snapshot_lastvisitedshop');
            $table->boolean('IS_EXCLUDED');
            $table->dateTime('CREATED_AT');

            $table->index(['SNAPSHOT_DATE', 'TICKET', 'SEGMENT_ID'], 'ix_t_grade_snapshot_snapshotdate_ticket_segmentid');
            $table->unique(['TICKET', 'SNAPSHOT_DATE'], 'uq_t_grade_snapshot_ticket_snapshotdate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_GRADE_SNAPSHOT');
    }
};
