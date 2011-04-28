<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

if ( empty( $_SESSION['post_token'] ) || $_SESSION['post_token'] !== $in['post_token'] ) {
	error('Twitterでの投稿が出来ませんでした');
}
unset( $_SESSION['post_token'] );

if ( empty( $_SESSION['tweet_msg'] ) ) {
	error('Twitterでの投稿が出来ませんでした');
}

if ( empty( $_SESSION['entry_content'] ) ) {
	error('コメントが空欄です');
}

//データベースに接続
$link = connect_db();

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	error('Twitterにログインされていません');
}

//メッセージに内容を付加してツイートを行う
$tweet = $in['entry_content'] . $in['tweet_msg'];
$error = post_tweet( $tweet, $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
if ( $error ) {
	error('Twitterのエラーのため、発言出来ませんでした。('.$error.')');
}

//直前のページに戻る
if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
	$auth_back_url = $_SERVER['HTTP_REFERER'];
	header('Location: ' . $auth_back_url);
}
else {
	header('Location: ' . $g_scripturl);
}
