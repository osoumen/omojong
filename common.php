<?php

require_once 'twitteroauth.php';
require_once 'globals.php';

function connect_db() {
	$dbServer = 'localhost';
	$g_dbuser = 'ojbot';
	$g_dbpassword = 'korogottu';

	if ( !$link = mysql_connect( $dbServer, $g_dbuser, $g_dbpassword ) ) {
		die('データベースに接続できませんでした');
	}
	mysql_select_db( G_DATABASE, $link );
	$sql = "SET NAMES utf8";
	$query = mysql_query( $sql, $link );
	return $link;
}

function is_exist_table( $link, $table_name ) {
	//テーブルの存在チェック
	$sql = sprintf("SHOW TABLES WHERE Tables_in_%s = '$table_name'", G_DATABASE);
	$query = mysql_query( $sql, $link );
	$exists = mysql_num_rows( $query );
	return $exists;
}

function get_new_session_key( $link, $leader_name ) {
	$session_key = 99999;
	//セッションテーブル内にleader_nameがあればそのセッションのキーを返す
	$sql = sprintf( "SELECT * FROM session WHERE leadername = %s", $leader_name );
	$query = mysql_query( $sql, $link );
	if ( $query ) {
		$exists = mysql_num_rows( $query );
	}
	else {
		$exists = 0;
	}
	if ( $exists > 0 ) {
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$session_key = $row['session_key'];
		}
	}
	else {
		//無ければ、カウンタを１進めて新しいのを返す
		$sql = sprintf( "SELECT * FROM global" );
		$query = mysql_query( $sql, $link );
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$session_key = $row['total'];
		}
		$session_key++;
		$sql = "UPDATE global SET total = $session_key";
		$query = mysql_query( $sql, $link );
	}

	return $session_key;
}

function load_session_table( $link ) {
	//GETにパラメータが指定されていればそちらを優先
	//cookieに今回読んだidを記録
	//GETが無い場合は、cookieから取得
	//どちらにも指定が無い場合は、NULLを返す
	//存在しないidを指定された場合はエラー表示

	global $words_table_name;
	global $members_table_name;
	global $kaitou_table_name;
	global $gameid_param_name;
	
	$session = array();
	
	if ( isset($_GET[$gameid_param_name]) ) {
		//GET値が指定されている
		$session_key = $_GET[$gameid_param_name];
	}
	else {
		//cookieに値がある
		if ( isset( $_COOKIE[$gameid_param_name] ) ) {
			$session_key = $_COOKIE[$gameid_param_name];
		}
		else {
			//どちらにもない
			return NULL;
		}
	}
	
	//セッション情報を読み込む
	$sql = sprintf( "SELECT * FROM session WHERE session_key = %s", $session_key );
	$query = mysql_query( $sql, $link );
	if ( mysql_num_rows( $query ) == 0 ) {
		//cookieを削除してエラー表示
		setcookie( $gameid_param_name, '', time() - 3600 );
		error( '指定されたページは存在しないか、削除されました。' );
		return NULL;
	}
	
	//指定されたidが正しい場合のみcookieに記録する
	setcookie( $gameid_param_name, $session_key, time() + 3600 * 24 * 75 );	//75日有効
	
	$row = @mysql_fetch_array( $query, MYSQL_ASSOC );
	$session['leadername'] = $row['leadername'];
	$session['session_key'] = $row['session_key'];
	$session['phase'] = $row['phase'];
	$session['ninzuu'] = $row['ninzuu'];
	$session['ninzuu_max'] = $row['ninzuu_max'];
	$session['maisuu'] = $row['maisuu'];
	$session['change_quant'] = $row['change_quant'];
	$session['change_amount'] = $row['change_amount'];
	$words_table_name = $row['words_table_name'];
	$members_table_name = $row['members_table_name'];
	$kaitou_table_name = $row['kaitou_table_name'];
	
	return $session;
}

function store_session_table( $link, $session ) {
	global $words_table_name;
	global $members_table_name;
	global $kaitou_table_name;

	//テーブルが無ければ作成する
	$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `session` (
			leadername text,
			session_key text,
			phase text,
			ninzuu int,
			ninzuu_max int,
			maisuu int,
			change_quant int,
			change_amount int,
			words_table_name text,
			members_table_name text,
			kaitou_table_name text
			)');
	$query = mysql_query( $sql, $link );
	
	//セッション情報をクリアする
	$sql = sprintf( "DELETE FROM session WHERE session_key = %s", $session['session_key'] );
	$query = mysql_query( $sql, $link );
	
	//セッション情報を書き込む
	$sql = sprintf( "INSERT INTO session VALUES( '%s', '%s', '%s', %d, %d, %d, %d, %d, '%s', '%s', '%s' )",
	$session['leadername'],
	$session['session_key'],
	$session['phase'],
	$session['ninzuu'],
	$session['ninzuu_max'],
	$session['maisuu'],
	$session['change_quant'],
	$session['change_amount'],
	$words_table_name,
	$members_table_name,
	$kaitou_table_name
	);
	$query = mysql_query( $sql, $link );
}

function load_members( $link, &$members, &$stock, &$changerest, &$change_amount ) {
	global $members_table_name;
	
	$members = array();
	$stock = array();
	$changerest = array();
	$change_amount = array();

	//参加者情報を読み込む
	$sql = sprintf( "SELECT * FROM %s", $members_table_name );
	$query = mysql_query( $sql, $link );
	while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
		$username = $row['username'];
		array_push($members, $username);
		$stock[$username] = $row['stock'];
		$changerest[$username] = $row['changerest'];
		$change_amount[$username] = $row['change_amount'];
	}
}

function numlist2sentence( $numlist, $words ) {
//	if ( count($words) == 0 ) {
//		load_words_table();
//	}
	$listwords = array();
	foreach ($numlist as $num) {
		$sent = $words[$num];
		array_push( $listwords, $sent );
	}
	return implode(" ", $listwords);
}

//使用可能な単語のリストを得る
function get_availablewordlist( $link, $members, $stock, $totalwords ) {
	global $kaitou_table_name;
	$usedlist = array();
	
	//使われている札の番号の配列を得る
	foreach ($members as $memb) {
		if ( empty( $stock[$memb] ) == FALSE ) {
			$memb_stock = explode(',', $stock[$memb] );
			foreach ( $memb_stock as $stock_id ) {
				$usedlist[] = $stock_id;
			}
		}
	}

	//投稿されている中に使用された札リストを得る
	$sql = sprintf( "SELECT wordlist FROM %s", $kaitou_table_name );
	$query = mysql_query( $sql, $link );
	while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
		$ans_used = explode(",", $row[0]);
		foreach ( $ans_used as $stock_id ) {
			$usedlist[] = $stock_id;
		}
	}
	//print_r( $usedlist );
	
	//usedlistを除いた札番号の配列を得る
	$wordnumber = array();
	for ( $i = 0; $i < $totalwords; $i++ ) {
		if ( in_array( $i, $usedlist ) == FALSE) {
			$wordnumber[] = $i;
		}
	}

	//シャッフルする
	shuffle($wordnumber);

	return $wordnumber;
}

function commit_mention($mlad,$inmsg) {
	$access_token        = '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT';
	$access_token_secret = '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg';
	
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token,$access_token_secret);
	
	// 投稿
	$notify_msg = "\@$mlad $inmsg";	
	$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>$notify_msg));	
	return $req;
}
/*
function is_member($name) {
	if ( in_array($name, $members) ) {
		return TRUE;
	}
	return FALSE;
}
*/

function store_members( $link, $members, $stock, $changerest, $change_amount ) {
	global $members_table_name;
	
	//参加者情報情報をクリアする
	$sql = sprintf( "DELETE FROM %s", $members_table_name );
	$query = mysql_query( $sql, $link );
	
	//参加者情報を書き込む
	foreach ($members as $memb) {
		$sql = sprintf("INSERT INTO %s VALUES( '%s', '%s', %d, %d )",
		$members_table_name,
		$memb,
		$stock[$memb],
		$changerest[$memb],
		$change_amount[$memb]
		);
		$query = mysql_query( $sql, $link );
	}
}

function load_words_table( $link, &$words ) {
	global $words_table_name;

	if ( count($words) == 0 ) {
		//単語を読み込む
		$sql = sprintf( "SELECT word FROM %s", $words_table_name );
		$query = mysql_query( $sql, $link );
		$words = array();
		if ( $query ) {
			while ( $row = mysql_fetch_array($query, MYSQL_NUM) ) {
				array_push( $words, $row[0] );
			}
		}
	}
	$totalwords = count($words);
	return $totalwords;
}

function get_todaywords( $link ) {
	global $words_table_name;
	//今日追加された単語数を取得する
	$sql = sprintf( "SELECT word FROM %s WHERE TO_DAYS( NOW() ) = TO_DAYS( date )", $words_table_name );
	$query = mysql_query( $sql, $link );
	$todaywords = mysql_num_rows( $query );
	return $todaywords;
}

function get_yesterdaywords( $link ) {
	global $words_table_name;
	//昨日追加された単語数を取得する
	$sql = sprintf( "SELECT word FROM %s WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1", $words_table_name );
	$query = mysql_query( $sql, $link );
	$yesterdaywords = mysql_num_rows( $query );
	return $yesterdaywords;
}

function refresh_kaitou_table( $link ) {
	global $kaitou_table_name;

	if ( is_exist_table( $link, $kaitou_table_name ) ) {
		//過去ログのファイル名をひとつずつ送る
		for ($numlogs=0; is_exist_table($link, sprintf( "%s_%d", $kaitou_table_name, $numlogs )); $numlogs++) {}
		for (; $numlogs>0; $numlogs--) {
			$oldnum = $numlogs-1;
			$sql = sprintf( "ALTER TABLE %s_$oldnum RENAME TO %s_$numlogs", $kaitou_table_name, $kaitou_table_name );
			$query = mysql_query( $sql, $link );
		}
		$sql = sprintf( "ALTER TABLE %s RENAME TO %s_0", $kaitou_table_name, $kaitou_table_name );
		$query = mysql_query( $sql, $link );
	}
}

function error( $msg ) {
	global $smarty;
	global $g_tpl_path;
	$smarty->assign( 'err_msg', $msg );
	$smarty->display( $g_tpl_path . 'page_error.tpl' );
	exit();
}

function message( $msg_title, $msg ) {
	global $smarty;
	global $g_tpl_path;
	$smarty->assign( 'msg_title', $msg_title );
	$smarty->assign( 'msg', $msg );
	$smarty->display( $g_tpl_path . 'page_message.tpl' );
	exit();
}
