<?php

session_start();
require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

//twitterにログインしているか調べる
$is_login = is_login();

//ゲーム情報を取り出す
$session = load_session_table( $link );

if ( isset( $session ) ) {
	//p値もしくは、cookieでページが指定されている
	$phase = $session['phase'];
}
else {
	//初めてこのURLを開いた、もしくはページの指定が不正
	$phase = 'login';
}

if ( $is_login || $phase == 'kekka' ) {
	switch ( $phase ) {
		case 'sanka':
			include 'html_sanka.php';
			mysql_close( $link );	//データベースを切断
			break;
			
		case 'toukou':
			include 'html_toukou.php';
			mysql_close( $link );	//データベースを切断
			break;
			
		case 'kekka':
			include 'html_kekka.php';
			mysql_close( $link );	//データベースを切断
			break;
			
		case 'login':
		default:
			mysql_close( $link );	//データベースを切断
			//新規開始ページへリダイレクト
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'page_start.php';
			header('HTTP/1.1 303 See Other');
			header("Location: http://$host$uri/$extra");
			exit;
	}
}
else {
	//twitterログインページを表示
	include 'html_login.php';
	mysql_close( $link );	//データベースを切断
}
