<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classification_rules', function (Blueprint $table) {
            $table->comment('記事の分類ルール');
            $table->string('id')->primary()->comment('分類ルールの一意識別子');
            $table->string('pattern')->comment('照合パターン');
            $table->boolean('is_regex')->default(false)->comment('パターンを正規表現として扱うかどうか');
            $table->string('label')->comment('一致した記事に付与するラベル');
            $table->string('match_field')->comment('照合対象フィールド');
            $table->boolean('enabled')->default(true)->comment('ルールが有効かどうか');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_rules');
    }
};
