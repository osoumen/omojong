<?php

require_once 'globals.php';
require_once 'common.php';

$in = $_REQUEST;

//空白を挿入
$in['entry_content'] = ' ' . $in['entry_content'];

if ( empty( $_SESSION['is_last'] ) ) {
	echo 'エラーが発生しました。';
	exit;
}

if ( empty( $_SESSION['post_token'] ) || $_SESSION['post_token'] !== $in['post_token'] ) {
	echo 'ログインされていません。';
	exit;
}

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	echo 'ログインされていません。';
	exit;
}

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	echo 'ログインされていません。';
	exit;
}
$myname = $_SESSION['access_token']['screen_name'];

load_members( $link, $members, $stock, $changerest, $change_amount );

$result = multi_tweet( $members, $myname, $in['entry_content'], $in['post_msg'],
$_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret'] );

//メッセージを表示
if ( $result ) {
	echo 'Twitterのエラーのためツイートできませんでした。('.$result.')';
}
else {
	unset( $_SESSION['is_last'] );
	unset( $_SESSION['post_token'] );
	echo 'ok';
}