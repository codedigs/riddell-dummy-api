<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRosterApplicationSizeDesignStatusPdfUrlSignatureImageInCartItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->json('roster')->nullable()->default(null)->after("right_image");
            $table->enum('design_status', ["incomplete", "configuration error", "complete"])->after("application_size");
            $table->string('pdf_url')->nullable()->default(null)->after("design_status");
            $table->string('signature_image')->nullable()->default(null)->after("pdf_url");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(["roster", "design_status", "pdf_url", "signature_image"]);
        });
    }
}
