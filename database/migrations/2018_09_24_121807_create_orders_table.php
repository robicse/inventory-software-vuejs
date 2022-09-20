<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->string('order_type');
            $table->string('sub_total');
            $table->string('total_tax')->default(0);
            $table->float('due_amount')->default(0);
            $table->float('total');
            $table->string('type');
            $table->float('profit')->default(0);
            $table->string('status'); //done,hold
            $table->integer('all_discount')->default(0); //done,hold
            $table->integer('customer_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('branch_id');
            $table->integer('transfer_branch_id')->nullable();
            $table->integer('created_by');
            $table->string('invoice_id');
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
        Schema::dropIfExists('orders');
    }
}
