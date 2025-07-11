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
        Schema::table('T_USER_GRADE_SNAPSHOT', function (Blueprint $table) {
            $table->foreign(['SEGMENT_ID'], 'FK_SNAPSHOT_SEGMENT')->references(['SEGMENT_ID'])->on('SEGMENT_MASTER')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TICKET'], 'FK_SNAPSHOT_USER')->references(['TICKET'])->on('T_USER')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('T_USER_GRADE_SNAPSHOT', function (Blueprint $table) {
            $table->dropForeign('FK_SNAPSHOT_SEGMENT');
            $table->dropForeign('FK_SNAPSHOT_USER');
        });
    }
};
