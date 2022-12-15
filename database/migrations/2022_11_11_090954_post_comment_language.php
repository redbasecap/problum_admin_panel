<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PostCommentLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_comment_language', function (Blueprint $table) {
            $table->id();
            $table->string('language_unique_name')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('object_id')->default(0);
            $table->text('text')->nullable();
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
        Schema::table('post_comment_language', function (Blueprint $table) {
            //
        });
    }
}
