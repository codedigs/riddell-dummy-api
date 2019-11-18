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
            $table->bigInteger('cut_id')->nullable()->default(null)->comment("Group cut id");
            $table->bigInteger('style_id')->nullable()->default(null);
            $table->bigInteger('design_id')->nullable()->default(null);
            $table->string('front_image')->nullable()->default(null);
            $table->string('back_image')->nullable()->default(null);
            $table->string('left_image')->nullable()->default(null);
            $table->string('right_image')->nullable()->default(null);
            $table->json('roster')->nullable()->default(null);
            $table->json('application_size')->nullable()->default(null);
            $table->enum('design_status', ["complete", "incomplete", "configuration error"]);
            $table->string('pdf_url')->nullable()->default(null);
            $table->string('signature_image')->nullable()->default(null);
            $table->boolean('is_approved')->default(0);
            $table->boolean('has_change_request')->default(0);
            $table->boolean('has_pending_approval')->default(0);

            $table->string('line_item_id')->nullable()->default(null);
            $table->string('pl_cart_id_fk')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });

        // Schema::table('cart_items', function(Blueprint $table) {
        //     $table->foreign('pl_cart_id_fk')->references('pl_cart_id')->on('carts');
        // });
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
