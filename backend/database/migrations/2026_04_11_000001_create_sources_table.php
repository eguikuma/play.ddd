<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->comment('追跡対象のソース（RSSフィード等）');
            $table->string('id')->primary()->comment('ソースの一意識別子');
            $table->string('name')->comment('ソースの表示名');
            $table->string('url')->unique()->comment('フィードのURL');
            $table->string('kind')->comment('ソースの種別');
            $table->string('status')->comment('追跡状態');
            $table->integer('fetch_interval_minutes')->comment('取得間隔（分）');
            $table->dateTime('registered_at')->comment('ソースを登録した日時');
            $table->dateTime('last_fetched_at')->nullable()->comment('最後に取得を実行した日時');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
