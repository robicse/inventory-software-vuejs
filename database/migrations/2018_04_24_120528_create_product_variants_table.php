<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sku')->unique()->nullable();
            $table->integer('product_id');
            $table->string('variant_title')->nullable();
            $table->string('attribute_values')->nullable();
            $table->string('variant_details')->nullable();
            $table->float('purchase_price');
            $table->float('selling_price');
            $table->boolean('enabled')->default(1);
            $table->string('imageURL')->default('no_image.png');
            $table->string('bar_code')->nullable();
            $table->integer('re_order')->nullable();
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
        Schema::dropIfExists('product_variants');
    }
}
