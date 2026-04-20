<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_labels', function (Blueprint $table) {
            $table->comment('記事に付与されたラベル');
            $table->string('article_id')->comment('対象の記事ID');
            $table->string('value')->comment('ラベルの値');
            $table->primary(['article_id', 'value']);

            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_labels');
    }
};
