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
        Schema::create('T_MEMBER', function (Blueprint $table) {
            $table->integer('TICKET');
            $table->string('ID', 25);
            $table->string('PASSWORD', 25);
            $table->dateTime('BIRTHDAY');
            $table->integer('SEX');
            $table->string('NICKNAME', 100)->nullable();
            $table->dateTime('INSERTDATE')->index('index_t_member');
            $table->dateTime('UPDATE')->nullable();
            $table->string('GLOBAL_IP', 16);
            $table->integer('RANK');
            $table->integer('PAS_COMPLETE');
            $table->integer('FIRST_SHOP_ID')->nullable();
            $table->integer('RESERVE1')->nullable();
            $table->integer('RESERVE2')->nullable();
            $table->integer('BLACK')->nullable();
            $table->integer('約款参照FLG')->nullable();

            $table->primary(['TICKET'], 'pk__t_member__922d1f591c00dc09');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_MEMBER');
    }
};
