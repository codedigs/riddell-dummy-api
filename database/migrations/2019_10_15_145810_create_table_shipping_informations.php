<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableShippingInformations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_informations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('school_name', 100)->nullable()->default(null);
            $table->string('first_name', 50)->nullable()->default(null);
            $table->string('last_name', 50)->nullable()->default(null);
            $table->string('email', 50)->nullable()->default(null);
            $table->string('business_phone', 20)->nullable()->default(null);
            $table->string('address_1')->nullable()->default(null);
            $table->string('address_2')->nullable()->default(null);
            $table->string('city', 20)->nullable()->default(null);
            $table->string('state', 20)->nullable()->default(null);
            $table->string('zip_code', 10)->nullable()->default(null);

            $table->bigInteger('user_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('shipping_informations', function(Blueprint $table) {
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
        Schema::dropIfExists('shipping_informations');
    }
}
