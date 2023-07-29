<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('thumbnail');
            $table->integer('user_id');
            $table->text('description');
            $table->text('content');
            $table->text('seo_content');
            $table->text('seo_description');
            $table->string('seo_title');
            $table->text('slug');
            $table->softDeletes();
            $table->enum('status', ['unpublished', 'published', 'draft', 'pending'])->default('pending');
            $table->text('type');
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
        Schema::dropIfExists('articles');
    }
}
