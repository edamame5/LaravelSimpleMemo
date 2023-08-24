<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    public function getMyMemo(){
        $query_tag = \Request::query('tag');
        // ＝＝＝ ベースのメソッド ＝＝＝
            // タグが無ければすべてのメモ取得
        $query = Memo::query()->select('memos.*')
            ->where('user_id',"=", \Auth::id())//loginユーザーのものを取得
            ->whereNull('deleted_at')//論理削除されているものをはじく
            ->orderBy('updated_at','DESC');//ASC:小さい順昇順・DESC:大きい順降順
        // ＝＝＝ ベースのメソッドここまで ＝＝＝

        // もしクエリパラメータtagがあればタグで絞り込み
        if( !empty($query_tag) ){
            $query->leftjoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')//tagが紐づいている行のみをカウントする
            ->where('memo_tags.tag_id', "=", $query_tag);
        }
        $memos= $query->get(); 

        return $memos;//ファンクションにおけるreturnとは呼び出し元に返る値のこと
    }
}
