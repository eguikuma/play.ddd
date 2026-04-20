<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fetch_executions', function (Blueprint $table) {
            $table->comment('ソースごとの取得実行記録');
            $table->string('id')->primary()->comment('取得実行の一意識別子');
            $table->string('source_id')->comment('取得対象ソースのID');
            $table->string('status')->comment('実行状態');
            $table->integer('new_article_count')->default(0)->comment('新規収集した記事数');
            $table->integer('skipped_article_count')->default(0)->comment('重複のためスキップした記事数');
            $table->dateTime('started_at')->comment('取得実行を開始した日時');
            $table->dateTime('finished_at')->nullable()->comment('取得実行が完了した日時');
            $table->text('failure_reason')->nullable()->comment('取得失敗の理由');

            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->index('source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fetch_executions');
    }
};
