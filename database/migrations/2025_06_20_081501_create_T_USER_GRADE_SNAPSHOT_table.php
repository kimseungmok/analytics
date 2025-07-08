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
        Schema::create('T_USER_GRADE_SNAPSHOT', function (Blueprint $table) {
            $table->bigIncrements('SNAPSHOT_ID');
            $table->integer('TICKET');
            $table->date('SNAPSHOT_DATE');
            $table->integer('SEGMENT_ID')->nullable()->index('idx_t_user_grade_snapshot_segment_id');
            $table->integer('VISIT_COUNT_1Y')->nullable();
            $table->integer('VISIT_COUNT_2Y')->nullable();
            $table->date('LAST_VISIT_DATE')->nullable();
            $table->boolean('IS_EXCLUDED')->default(false);
            $table->dateTime('CREATED_AT')->useCurrent();

            $table->index(['SNAPSHOT_DATE', 'SEGMENT_ID'], 'idx_date_segment');
            $table->primary(['SNAPSHOT_ID'], 'pk__t_user_g__9c98b4d6419ba9e6');
            $table->unique(['TICKET', 'SNAPSHOT_DATE'], 'uq_ticket_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_USER_GRADE_SNAPSHOT');
    }
};
