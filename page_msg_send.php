<?php

require_once 'globals.php';
require_once 'common.php';

$in = $_REQUEST;

//空白を挿入
$in['entry_content'] = ' ' . $in['entry_content'];

if ( empty( $_SESSION['is_last'] ) ) {
	header('Location: ' . $g_scripturl);
}
unset( $_SESSION['is_last'] );

if ( empty( $_SESSION['post_token'] ) || $_SESSION['post_token'] !== $in['post_token'] ) {
	error('Twitterへの投稿が出来ませんでした');
}
unset( $_SESSION['post_token'] );

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
	$error = '';
	if ( $memb != $myname ) {
		//合計文字数140文字をオーバーしていたら本文を縮める
		$max_len = 140 - mb_strlen( '@' . $memb . $in['post_msg'] );
		$inmsg = mb_strimwidth( $in['entry_content'], 0, $max_len, '…' );
		$msg = $inmsg . $in['post_msg'];
	
		if ( $use_useraccount_for_mension ) {
			$error = commit_mention( $memb, $msg, $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
		}
		else {
			$error = commit_mention( $memb, $msg );
		}
	}
	if ( $error ) {
		error('Twitterのエラーのため処理されませんでした。('.$error.')');
	}
}

//ページを表示
$pagetitle = 'メッセージの送信';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'page_msg_send.tpl' );
