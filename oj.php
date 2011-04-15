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
	$phase = 'login';
	//初めてこのURLを開いた、もしくはページの指定が不正
	//ログイン済みの場合、参加中のゲームを探す
	$session_key_list = array();
	$phase_list = array();
	$memberlist_list = array();

	if ( $is_login ) {
		$sql = 'SELECT session_key,phase,members_table_name FROM session';
		$query = mysql_query( $sql, $link );
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			//自分の名前が含まれているメンバーリストを探す
			$sql = sprintf('SELECT username FROM %s WHERE username=\'%s\'', $row['members_table_name'], $_SESSION['access_token']['screen_name']);
			$query2 = mysql_query( $sql, $link );
			if ( mysql_num_rows( $query2 ) > 0 ) {
				//フェーズ、session_key、参加者リストを保存
				$session_key_list[] = $row['session_key'];
				$phase_list[] = $row['phase'];
				$memberlist = array();
				$sql = sprintf('SELECT username FROM %s', $row['members_table_name']);
				$query3 = mysql_query( $sql, $link );
				while ( $row2 = mysql_fetch_array( $query3, MYSQL_NUM ) ) {
					$memberlist[] = $row2[0];
				}
				$memberlist_list[] = $memberlist;
			}
		}
		if ( count($session_key_list) > 0 ) {
			//自分が参加しているリストを表示する
			include 'html_sanka_list.php';
			exit;
		}
	}
}

if ( $is_login || $phase == 'kekka' ) {
	switch ( $phase ) {
		case 'sanka':
			include 'html_sanka.php';
			mysql_close( $link );	//データベースを切断
			break;
			
		case 'deal':
			mysql_close( $link );	//データベースを切断
			//札を配るページへリダイレクト
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'page_deal.php';
			header('HTTP/1.1 303 See Other');
			header("Location: http://$host$uri/$extra");
			exit;
		
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
