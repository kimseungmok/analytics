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
        Schema::create('T_MEMBER_INFO', function (Blueprint $table) {
            $table->string('TICKET', 50)->nullable();
            $table->string('ID', 50)->nullable();
            $table->string('PASSWORD', 50)->nullable();
            $table->string('BIRTHDAY', 50)->nullable();
            $table->string('SEX', 50)->nullable();
            $table->string('NICKNAME', 50)->nullable();
            $table->string('INSERTDATE', 50)->nullable();
            $table->string('UPDATE', 50)->nullable();
            $table->string('GLOBAL_IP', 50)->nullable();
            $table->string('RANK', 50)->nullable();
            $table->string('PAS_COMPLETE', 50)->nullable();
            $table->string('FIRST_SHOP_ID', 50)->nullable();
            $table->string('RESERVE1', 50)->nullable();
            $table->string('RESERVE2', 50)->nullable();
            $table->string('BLACK', 50)->nullable();
            $table->string('約款参照FLG', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_MEMBER_INFO');
    }
};
