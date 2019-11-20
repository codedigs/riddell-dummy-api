<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldGroupCutIdInCutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cuts', function (Blueprint $table) {
            $table->integer("group_cut_id")->nullable()->default(null)->after("sport");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cuts', function (Blueprint $table) {
            $table->dropColumn("group_cut_id");
        });
    }
}
