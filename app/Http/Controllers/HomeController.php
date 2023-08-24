<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//modelをインポート
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //メモを取得するのは、Providers/AppServiceProvider.phpのViewComposerで設定

        $tags = Tag::where('user_id', '=', \Auth::id()) -> whereNull('deleted_at')->orderBy('id','DESC')
        ->get();   
            //dd($tags);
        return view('create', compact( 'tags'));
    }

    public function store(Request $request) //新規作成時利用
    {
        $posts = $request -> all();
        $request->validate([ 'content' => 'required' ]);
        //dd=dump dieの略 → メソッドの引数の摂った数を展開して止める。→データ確認用
        //dd($posts);

        // ===== ここからトランザクション開始 ======
        DB::transaction(function () use($posts){
              // メモIDをインサートして取得
            $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);
            $tag_exsists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])
            ->exists();
            // 新規タグが入力されているかチェック
            // 新規タグが既にtagsテーブルに存在するのかチェック
            if( !empty($posts['new_tag']) && !$tag_exsists ){
                // 新規タグが既に存在しなければ、tagsテーブルにインサート→IDを取得
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                // memo_tagsにインサートして、メモとタグを紐付ける
                 MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
            //既存タグが紐づけられた場合-memo_tagsにインサート
            if( !empty($posts['tags'][0])){
                foreach($posts['tags'] as $tag){
                    MemoTag::insert(['memo_id' => $memo_id , 'tag_id' => $tag]);
                }
            }
        });   
        // ===== ここまでがトランザクションの範囲 ======
            //tagを使わない場合は単純なインサートでOK
            // Memo::insert(['Content'=> $posts['content'], 'user_id' => \Auth::id()]);
        return redirect( route('home'));
    }

    public function edit($id)
    {
        //メモを取得するのは、Providers/AppServiceProvider.phpのViewComposerで設定

        $edit_memo = Memo::select('memos.*','tags.id AS tag_id')
                //第一引数に結合するテーブル名、第二引数に主テーブルの結合キー、第四引数に結合するテーブルの結合キーを記述
            ->leftJoin('memo_tags', 'memo_tags.memo_id' , '=' , 'memos.id') //memo_tagsテーブルの、
            ->leftJoin('tags','memo_tags.tag_id','tags.id')
            ->where('memos.id',"=", $id)
            ->where('memos.user_id',"=", \Auth::id())//loginユーザーのものを取得
            ->whereNull('memos.deleted_at')//論理削除されているものをはじく
            ->get();
                //dd($edit_memo[0]['id']);
        $include_tags = [];
        foreach($edit_memo as $memo){
            array_push($include_tags, $memo['tag_id']);
        }

        $tags = Tag::where('user_id', '=', \Auth::id()) -> whereNull('deleted_at')->orderBy('id','DESC')
        ->get(); 
        return view('edit', compact('edit_memo', 'include_tags','tags'));
    }

    public function update(Request $request)
    {
        $posts = $request -> all();
        $request->validate([ 'content' => 'required' ]);
                //dd($posts);
                //dd=dump dieの略 → メソッドの引数の摂った数を展開して止める。→データ確認用
                //dd(\Auth::id());
            // ===== ここからトランザクション開始 ======
            DB::transaction(function () use ($posts) {
                Memo::Where('id',$posts['memo_id'])->update(['Content'=> $posts['content']]);

                // 一旦メモとタグの紐づけを削除
                MemoTag::where('memo_id', "=", $posts['memo_id'])->delete();
                    // 再度メモとタグの紐づけ
                if( !empty($posts['tags'][0])){
                    foreach ($posts['tags'] as $tag){
                        MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag] );
                    }
                }
            // もし、新しいタグの入力があれば、インサートして紐づける
                $tag_exsists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])
                ->exists();
                // 新規タグが入力されているかチェック
                // 新規タグが既にtagsテーブルに存在するのかチェック
                if( !empty($posts['new_tag']) && !$tag_exsists ){
                    // 新規タグが既に存在しなければ、tagsテーブルにインサート→IDを取得
                    $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                    // memo_tagsにインサートして、メモとタグを紐付ける
                    MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag_id]);
                }
            });
            // トランザクションここまで============

        return redirect( route('home'));
    }

    public function destroy(Request $request)
    {
        $posts = $request -> all();
        //dd($posts);
        //dd=dump dieの略 → メソッドの引数の摂った数を展開して止める。→データ確認用
        //dd(\Auth::id());
        //Memo::Where('id',$posts['memo_id'])->delete()+ はNG。物理削除になる。
        //論理削除にしたい
        Memo::Where('id',$posts['memo_id'])->update(['deleted_at'=> date("Y-m-d H:i:s", time())]);

        return redirect( route('home'));
    }


}