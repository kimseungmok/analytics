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
        Schema::table('T_USER_LOG', function (Blueprint $table) {
            $table->foreign(['TICKET'], 'FK_USER_LOG_USER')->references(['TICKET'])->on('T_USER')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('T_USER_LOG', function (Blueprint $table) {
            $table->dropForeign('FK_USER_LOG_USER');
        });
    }
};
