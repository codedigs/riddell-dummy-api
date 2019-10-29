<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCuts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("cut_id")->nullable()->default(null)->comment("Cut id from backend");
            // $table->enum("style_category", ["", "jerseys", "pants"]);
            // $table->enum("gender", ["", "men", "women", "unisex", "youth"]);
            $table->json("hybris_sku")->nullable()->default(null);
            $table->string("style_category", 20)->nullable()->default(null);
            $table->json("gender")->nullable()->default(null);
            $table->string("name", 50)->nullable()->default(null);
            $table->string("image")->nullable()->default(null);
            $table->string("sport", 20)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cuts');
    }
}
