<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopPageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_page_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('area');
            $table->string('about');
            $table->text('summary');
            $table->string('language');
            $table->unsignedBigInteger('toppage_id');
            $table->foreign('toppage_id')->references('id')->on('toppages')->onDelete('cascade');
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
        Schema::dropIfExists('top_page_details');
    }
}
