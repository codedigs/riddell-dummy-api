<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCoachRequestLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coach_request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('cut_note')->nullable()->default(null);
            $table->text('style_note')->nullable()->default(null);
            $table->text('customizer_note')->nullable()->default(null);
            $table->text('roster_note')->nullable()->default(null);
            $table->text('application_size_note')->nullable()->default(null);
            $table->bigInteger('cart_item_id')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('coach_request_logs', function(Blueprint $table) {
            $table->bigInteger('cart_item_id')->unsigned()->change();

            $table->foreign('cart_item_id')->references('id')->on('cart_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coach_request_logs');
    }
}
