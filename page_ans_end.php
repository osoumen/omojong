<?php

require_once 'globals.php';
require_once 'common.php';

session_start();
if ( empty( $_SESSION['is_last'] ) ) {
	header('Location: ' . $g_scripturl);
}
unset( $_SESSION['is_last'] );

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
$myname = $_SESSION['access_token']['screen_name'];

load_members( $link, $members, $stock, $changerest, $change_amount );

foreach ( $members as $memb ) {
	if ( $memb != $myname ) {
		if ( $use_useraccount_for_mension ) {
			$error = commit_mention( $memb, $notifymsg2 . $session['session_key'], $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
		}
		else {
			$error = commit_mention( $memb, $notifymsg2 . $session['session_key'] );
		}
	}
	if ( $error ) {
		error('Twitterのエラーのため、発言出来ませんでした。('.$error.')');
	}
}



//ページを表示
$pagetitle = 'メッセージの送信';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'page_msg_send.tpl' );
