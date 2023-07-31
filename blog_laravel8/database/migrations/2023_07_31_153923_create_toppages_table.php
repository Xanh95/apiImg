<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateToppagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toppages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->string('area');
            $table->string('about');
            $table->text('summary');
            $table->integer('cover_photo')->nullable();
            $table->integer('avatar')->nullable();
            $table->integer('video')->nullable();
            $table->text('website')->nullable();
            $table->text('facebook')->nullable();
            $table->text('instagram')->nullable();
            $table->enum('status', ['unpublished', 'published'])->default('unpublished');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('toppages');
    }
}
