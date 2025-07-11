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
        Schema::create('T_MEMBER_ACC', function (Blueprint $table) {
            $table->string('user_id', 50)->nullable();
            $table->string('usage_time', 50)->nullable();
            $table->string('shop_id', 50)->nullable();
            $table->string('edit_time', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_MEMBER_ACC');
    }
};
