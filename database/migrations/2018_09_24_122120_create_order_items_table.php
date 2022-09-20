<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('variant_id');
            $table->string('type')->nullable();
            $table->float('quantity');
            $table->float('price');
            $table->float('discount')->default(0);
            $table->float('sub_total')->default(0);
            $table->integer('tax_id')->nullable();
            $table->integer('order_id');
            $table->string('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
