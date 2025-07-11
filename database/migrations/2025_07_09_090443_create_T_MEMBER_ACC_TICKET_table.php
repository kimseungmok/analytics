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
        Schema::create('T_MEMBER_ACC_TICKET', function (Blueprint $table) {
            $table->string('TICKET', 50)->nullable();
            $table->string('ID', 50)->nullable();
            $table->string('USAGE_TIME', 50)->nullable();
            $table->string('SHOP_ID', 50)->nullable();
            $table->string('RECEIPT_NUMBER', 50)->nullable();
            $table->string('VISITED', 50)->nullable();
            $table->string('EDIT_TIME', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_MEMBER_ACC_TICKET');
    }
};
