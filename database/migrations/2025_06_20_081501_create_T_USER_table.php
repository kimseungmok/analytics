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
        Schema::create('T_USER', function (Blueprint $table) {
            $table->increments('TICKET');
            $table->string('ID', 25)->unique('uq__t_user__3214ec26e2009d3e');
            $table->date('BIRTHDAY')->nullable();
            $table->string('SEX', 1)->nullable();
            $table->dateTime('INSERTDATE')->useCurrent();
            $table->integer('FIRST_SHOP_ID')->nullable();
            $table->boolean('IS_ACTIVE')->default(true);

            $table->primary(['TICKET'], 'pk__t_user__922d1f5924e5574d');
            $table->unique(['ID'], 'ux_t_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_USER');
    }
};
