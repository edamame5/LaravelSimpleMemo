<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memos', function (Blueprint $table) {
            //unsignedBigInteger とは+-のつかない整数。参照するときに型が同じではないと一致しない。
            //第2引数の true は、自動的に値を+1していってくれる。、
            $table->unsignedBigInteger('id',true);
            //longText は長文を格納できる
            $table->longText('content');
            $table->unsignedBigInteger('user_id');
            // 論理削除を定義→deleted_atを自動生成
            $table->softDeletes();
            // timestamp と書いてしまうと、レコード挿入時、更新時に値が入らない為、DB::rawで直接書く
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            //id参照のための制限を作成
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memos');
    }
}
