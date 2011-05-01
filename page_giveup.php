<?php

require_once 'globals.php';
require_once 'common.php';

$session = array();

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//--エラーチェック--
if ( $session['phase'] !== 'toukou' ) {
	error("現在解答を受け付けていません。");
}
if ( in_array($c_username, $members) == FALSE ) {
	error("参加していません。");
}
//持ち札があるか
if ( mb_strlen($stock[$c_username]) == 0 ) {
	error( $c_username . 'さんの解答は終了しています');
}

$is_last = '';

//ページを表示
if ( isset($in['confirm']) ) {
	$pagetitle = '解答の終了';
	$smarty->assign( 'pagetitle', $pagetitle );
	$smarty->display( $g_tpl_path . 'page_giveup_confirm.tpl' );
}
else {
	//持ち札を空にする
	$stock[$c_username] = '';
	
	//交換回数を０にする
	$changerest[$c_username] = 0;
	$change_amount[$c_username] = 0;
	
	//全員の解答が終了したか調べてモード遷移を行う
	$remain = 0;
	foreach ( $members as $memb ) {
		if ( $stock[$memb] ) {
			$remain++;
		}
	}
	if ($remain === 0) {
		$session['phase'] = 'kekka';
		if ( $usenotification2 ) {
			$is_last = 1;
			$_SESSION['is_last'] = 1;
		}
		//解答をログへ移動
		$table_name = push_kaitou_table_pastlog( $link, $kaitou_table_name );
		$kaitou_table_name = $table_name;
	}
	store_session_table( $link, $session );
	store_members( $link, $members, $stock, $changerest, $change_amount );

	if ( $is_last ) {
		$pagetitle = '解答の終了';
		$smarty->assign( 'pagetitle', $pagetitle );
		
		$message = '全員の解答が終わりました。';
		$smarty->assign( 'message', $message );
		
		$default_msg = $notifymsg2;
		$post_msg = $g_scripturl . '?p=' . $session['session_key'];
		//投稿用トークン生成
		$post_token = generate_post_token();
		$_SESSION['post_token'] = $post_token;
	
		$smarty->assign( 'default_msg', $default_msg );
		$smarty->assign( 'post_msg', $post_msg );
		$smarty->assign( 'post_token', $post_token );
		$smarty->display( $g_tpl_path . 'page_send_mention.tpl' );
	}
	else {
		header('Location: ' . $g_scripturl);
	}
}
