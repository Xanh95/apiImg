<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReversionArticleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reversion_article_details', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('reversion_article_id');
            $table->text('description');
            $table->text('content');
            $table->text('seo_content');
            $table->text('seo_description');
            $table->text('seo_title');
            $table->text('slug');
            $table->text('language');
            $table->foreign('reversion_article_id')->references('id')->on('reversion_articles')->onDelete('cascade');
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
        Schema::dropIfExists('reversion_article_details');
    }
}
