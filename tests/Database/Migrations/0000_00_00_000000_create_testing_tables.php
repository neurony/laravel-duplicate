<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('intro')->nullable();
            $table->text('content')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('approved')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('post_review', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned()->index();
            $table->string('name');
            $table->text('content')->nullable();
            $table->integer('rating')->default(0);
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned()->index();
            $table->string('subject');
            $table->text('comment')->nullable();
            $table->integer('votes')->default(0);
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['post_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('post_comment_replies');
        Schema::dropIfExists('post_comments');
        Schema::dropIfExists('post_review');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('posts');
    }
}
