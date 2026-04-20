<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->comment('ソースから収集し、ユーザーが閲覧・管理する記事');
            $table->string('id')->primary()->comment('記事の一意識別子');
            $table->string('source_id')->comment('取得元ソースのID');
            $table->string('source_kind')->comment('取得元ソースの種別');
            $table->string('title')->comment('記事タイトル');
            $table->string('url')->comment('記事のURL');
            $table->text('body')->nullable()->comment('記事の本文');
            $table->string('fingerprint')->unique()->comment('重複排除用ハッシュ値');
            $table->dateTime('published_at')->nullable()->comment('記事の公開日時');
            $table->dateTime('collected_at')->comment('denが記事を収集した日時');
            $table->string('reading_status')->default('unread')->comment('既読状態');
            $table->boolean('bookmarked')->default(false)->comment('ブックマーク済みかどうか');
            $table->dateTime('read_at')->nullable()->comment('記事を既読にした日時');

            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->index('source_id');
            $table->index('reading_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
