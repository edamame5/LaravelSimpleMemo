function deleteHandle(event){
    // 一旦フォームをストップ
    event.preventDefault();
    if(window.confirm('本当に削除していいですか？')){
        //削除OKならFormを再開
        document.getElementById('delete_form').submit();
    }else{
        alert('キャンセルしました')
    }
}
