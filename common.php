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

function load_session_table( $link ) {
	$session = array();
	
	//セッション情報を読み込む
	$sql = "SELECT * FROM session";
	$query = mysql_query( $sql, $link );
	$row = @mysql_fetch_array( $query, MYSQL_ASSOC );
	$session['leadername'] = $row['leadername'];
	$session['phase'] = $row['phase'];
	$session['ninzuu'] = $row['ninzuu'];
	$session['ninzuu_max'] = $row['ninzuu_max'];
	$session['maisuu'] = $row['maisuu'];
	$session['change_quant'] = $row['change_quant'];
	$session['change_amount'] = $row['change_amount'];
	
	return $session;
}

function load_members( $link, &$members, &$stock, &$changerest, &$change_amount ) {
	$members = array();
	$stock = array();
	$changerest = array();
	$change_amount = array();

	//参加者情報を読み込む
	$sql = "SELECT * FROM members";
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
	$usedlist = array();
	
	//使われている札の番号の配列を得る
	foreach ($members as $memb) {
		array_push($usedlist, explode(",", $stock[$memb] ) );
	}

	//投稿されている中に使用された札リストを得る
	$sql = "SELECT wordlist FROM kaitou";
	$query = mysql_query( $sql, $link );
	while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
		array_push( $usedlist,explode(",", $row[0]) );
	}

	//usedlistを除いた札番号の配列を得る
	$wordnumber = array();
	for ( $i = 0; $i < $totalwords; $i++ ) {
		if ( in_array( $i, $usedlist ) == FALSE) {
			array_push( $wordnumber, $i );
		}
	}

	//シャッフルする
	shuffle($wordnumber);

	return $wordnumber;
}

function commit_mention($mlad,$inmsg) {
	$consumer_key    = 'oVHQOYjXkfrEOGEVdRosQ';
	$consumer_secret = '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8';
	$access_token        = '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT';
	$access_token_secret = '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg';
	
	// OAuthオブジェクト生成
	$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
	
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
function store_session_table( $link, $session ) {
	//セッション情報をクリアする
	$sql = "DELETE FROM session";
	$query = mysql_query( $sql, $link );
	
	//セッション情報を書き込む
	$sql = sprintf( "INSERT INTO session VALUES( '%s', '', '%s', %d, %d, %d, %d, %d )",
	$session['leadername'],
	$session['phase'],
	$session['ninzuu'],
	$session['ninzuu_max'],
	$session['maisuu'],
	$session['change_quant'],
	$session['change_amount']
	);
	$query = mysql_query( $sql, $link );
}

function store_members( $link, $members, $stock, $changerest, $change_amount ) {
	//参加者情報情報をクリアする
	$sql = "DELETE FROM members";
	$query = mysql_query( $sql, $link );
	
	//参加者情報を書き込む
	foreach ($members as $memb) {
		$sql = sprintf("INSERT INTO members VALUES( '%s', '%s', %d, %d )",
		$memb,
		$stock[$memb],
		$changerest[$memb],
		$change_amount[$memb]
		);
		$query = mysql_query( $sql, $link );
	}
}

function load_words_table( $link, &$words ) {
	if ( count($words) == 0 ) {
		//単語を読み込む
		$sql = "SELECT word FROM words";
		$query = mysql_query( $sql, $link );
		$words = array();
		while ( $row = mysql_fetch_array($query, MYSQL_NUM) ) {
			array_push( $words, $row[0] );
		}
	}
	$totalwords = count($words);
	return $totalwords;
}

function get_todaywords( $link ) {
	//今日追加された単語数を取得する
	$sql = "SELECT word FROM words WHERE TO_DAYS( NOW() ) = TO_DAYS( date )";
	$query = mysql_query( $sql, $link );
	$todaywords = mysql_num_rows( $query );
	return $todaywords;
}

function get_yesterdaywords( $link ) {
	//昨日追加された単語数を取得する
	$sql = "SELECT word FROM words WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1";
	$query = mysql_query( $sql, $link );
	$yesterdaywords = mysql_num_rows( $query );
	return $yesterdaywords;
}

function refresh_kaitou_table( $link ) {
	if ( is_exist_table( $link, "kaitou" ) ) {
		//過去ログのファイル名をひとつずつ送る
		for ($numlogs=0; is_exist_table($link, "kaitou_$numlogs"); $numlogs++) {}
		for (; $numlogs>0; $numlogs--) {
			$oldnum = $numlogs-1;
			$sql = "ALTER TABLE kaitou_$oldnum RENAME TO kaitou_$numlogs";
			$query = mysql_query( $sql, $link );
		}
		$sql = "ALTER TABLE kaitou RENAME TO kaitou_0";
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
