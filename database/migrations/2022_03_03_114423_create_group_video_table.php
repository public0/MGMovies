<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies');

            $table->string('type')->index();
            $table->string('title');
            $table->string('thumbnail');
            $table->string('url');
        });

        Schema::table('videos', function($table) {
            $table->unsignedBigInteger('group_video_id');
            $table->foreign('group_video_id')->references('id')->on('group_videos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('videos');
        Schema::dropIfExists('group_videos');
        Schema::enableForeignKeyConstraints();
    }
};
