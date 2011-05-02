<?php

require_once 'globals.php';
require_once 'common.php';

$link = connect_db();
$session = load_session_table( $link );

if ( $session == NULL ) {
	error('idが指定されていません');
}

$id = $session['leadername'];

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	error('Twitterにログインされていません');
}

//認証情報を利用してフォローを行う
$error = follow_id( $id, $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
if ( $error ) {
	//error('Twitterのエラーのため、処理出来ませんでした。('.$error.')');
	error('Twitterのエラーのため、処理出来ませんでした。');
}

//直前のページに戻る
redirect_to_prevpage();
