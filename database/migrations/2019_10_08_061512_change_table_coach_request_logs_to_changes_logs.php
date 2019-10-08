<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableCoachRequestLogsToChangesLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('coach_request_logs');

        Schema::create('changes_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string("note")->nullable()->default(null);
            $table->text("attachments")->nullable()->default(null)->comment("Comma separated value");
            $table->enum("role", ["sales rep", "coach"]);
            $table->enum("type", ["fixed", "ask for changes", "quick change"]);
            $table->bigInteger('cart_item_id')->nullable()->default(null);
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

        Schema::dropIfExists('changes_logs');
    }
}
