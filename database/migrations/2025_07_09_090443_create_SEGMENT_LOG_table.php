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
        Schema::create('SEGMENT_LOG', function (Blueprint $table) {
            $table->integer('ID');
            $table->integer('USAGE_COUNT');
            $table->date('REFERENCE_DATE');
            $table->tinyInteger('SEGMENT_NUMBER');
            $table->string('TICKET', 50)->nullable();

            $table->index(['ID', 'REFERENCE_DATE'], 'idx_segment_log_id_date');
            $table->primary(['ID', 'REFERENCE_DATE'], 'pk__segment___a34f9a178bd630e7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SEGMENT_LOG');
    }
};
