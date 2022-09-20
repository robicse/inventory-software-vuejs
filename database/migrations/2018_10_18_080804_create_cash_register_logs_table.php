<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashRegisterLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_register_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->integer('cash_register_id');
            $table->double('opening_amount')->nullable();
            $table->double('closing_amount')->nullable();
            $table->string('status');
            $table->dateTime('opening_time')->nullable();
            $table->dateTime('closing_time')->nullable();
            $table->integer('opened_by')->nullable();
            $table->integer('closed_by')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_register_logs');
    }
}
