<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token')->nullable()->default(null);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_abandoned')->default(false);

            $table->bigInteger('user_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('carts', function(Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->change();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
