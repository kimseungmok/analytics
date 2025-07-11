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
        Schema::create('SEGMENT_MASTER', function (Blueprint $table) {
            $table->increments('SEGMENT_ID');
            $table->integer('SEGMENT_NUMBER');
            $table->string('SEGMENT_NAME', 20);
            $table->string('DESCRIPTION')->nullable();
            $table->boolean('IS_ACTIVE')->default(true);
            $table->dateTime('CREATED_AT')->useCurrent();
            $table->dateTime('UPDATED_AT')->useCurrent();

            $table->primary(['SEGMENT_ID'], 'pk__segment___11056e47c8164c24');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SEGMENT_MASTER');
    }
};
