<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReversionArticleMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reversion_article_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reversion_article_id');
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
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
        Schema::dropIfExists('reversion_article_metas');
    }
}
