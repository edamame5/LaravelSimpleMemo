<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Memo;
use App\Models\Tag; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
            //全てのメソッドが呼ばれる前に先に呼ばれるメソッド
            view()->composer('*', function ($view){
                //自分のメモ取得はMemoモデルに任せる
                // インスタンス化
                $memo_model = New Memo();
                //メモ取得
                $memos = $memo_model->getMyMemo();

               
                

                $tags = Tag::where('user_id',"=", \Auth::id())
                     ->whereNull('deleted_at')
                     ->orderBy('id','DESC')
                     ->get();

                $view->with('memos',$memos)->with('tags',$tags);
                //(第1引数はViewで使う時の命名、第2引数は渡したい変数or配列)
            });
}

}