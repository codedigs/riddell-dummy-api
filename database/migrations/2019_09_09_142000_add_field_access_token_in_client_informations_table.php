<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldAccessTokenInClientInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_informations', function (Blueprint $table) {
            $table->text("approval_token")->nullable()->default(null)->after("zip_code");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_informations', function (Blueprint $table) {
            $table->text("approval_token")->nullable()->default(null)->after("zip_code");
        });
    }
}
