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
        Schema::create('T_USER_LOG', function (Blueprint $table) {
            $table->bigIncrements('LOG_ID');
            $table->integer('TICKET');
            $table->string('ID', 25);
            $table->dateTime('VISIT_TIME')->index('idx_t_user_log_visit_time');
            $table->integer('SHOP_ID')->nullable();
            $table->string('RECEIPT_NUMBER', 50)->nullable();
            $table->boolean('VISITED')->default(true);
            $table->dateTime('EDIT_TIME')->useCurrent();

            $table->index(['TICKET', 'VISIT_TIME'], 'idx_t_user_log_ticket_visit_time');
            $table->primary(['LOG_ID'], 'pk__t_user_l__4364c8828db09abe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('T_USER_LOG');
    }
};
