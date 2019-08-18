<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCartItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cut_id')->nullable()->default(null);
            $table->bigInteger('design_id')->nullable()->default(null);
            $table->string('customizer_url')->nullable()->default(null);
            $table->boolean('is_approved')->default(0);
            $table->boolean('has_change_request')->default(0);
            $table->boolean('has_pending_approval')->default(0);

            $table->bigInteger('cart_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('cart_items', function(Blueprint $table) {
            $table->bigInteger('cart_id')->unsigned()->change();

            $table->foreign('cart_id')->references('id')->on('carts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
