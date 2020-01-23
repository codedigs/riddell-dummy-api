<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCartItemsSide2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items_side2', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('style_id')->nullable()->default(null);
            $table->bigInteger('design_id')->nullable()->default(null);
            $table->longText('builder_customization')->nullable()->default(null);
            $table->string('front_image')->nullable()->default(null);
            $table->string('back_image')->nullable()->default(null);
            $table->string('left_image')->nullable()->default(null);
            $table->string('right_image')->nullable()->default(null);
            $table->json('application_size')->nullable()->default(null);

            $table->bigInteger('cart_item_id')->nullable()->unsigned()->default(null);
            $table->foreign('cart_item_id')->references('id')->on('cart_items');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items_side2');
    }
}
