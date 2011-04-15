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
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
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

//ページを表示
if ( isset($in['confirm']) ) {
	$smarty->display( $g_tpl_path . 'page_giveup_confirm.tpl' );
}
else {
	//持ち札を空にする
	$stock[$c_username] = '';
	
	//全員の解答が終了したか調べてモード遷移を行う
	$remain = 0;
	foreach ( $members as $memb ) {
		if ( $stock[$memb] ) {
			$remain++;
		}
	}
	if ($remain === 0) {
		$session['phase'] = 'kekka';
		if ( $usenotification ) {
			foreach ( $members as $memb ) {
				if ( $memb !== $c_username ) {
					commit_mention( $memb, $notifymsg2 );
				}
			}
		}
		//解答をログへ移動
		$table_name = push_kaitou_table_pastlog( $link, $kaitou_table_name );
		$kaitou_table_name = $table_name;
	}
	store_session_table( $link, $session );
	store_members( $link, $members, $stock, $changerest, $change_amount );

	$smarty->assign( 'c_username', $c_username );
	$smarty->display( $g_tpl_path . 'page_giveup.tpl' );
}

//データベースを切断
mysql_close( $link );
