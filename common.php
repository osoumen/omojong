<?php

require_once("twitteroauth.php");

function connect_db {
	$dbServer = 'localhost';
	if ( !$link = mysql_connect( $dbServer, $g_dbuser, $g_dbpassword ) ) {
		die('データベースに接続できませんでした');
	}
	mysql_select_db( $g_database, $link );
	mysql_set_charset( 'utf8', $link );
	
	return $link;
}

function is_exist_table( $link, $table_name ) {
	#テーブルの存在チェック
	$sql = "SHOW TABLES WHERE Tables_in_$g_database = '$table_name';";
	$query = mysql_query( $sql, $link );
	$exists = mysql_num_rows( $query );
	return $exists;
}

function load_session_table {
	$session = array();
	$members = array();
	$stock = array();
	$changerest = array();
	
	#データベースに接続
	$link = connect_db();

	#セッション情報を読み込む
	$sql = "SELECT * FROM session;";
	$query = mysql_query( $sql, $link );
	$row = @mysql_fetch_array( $query, MYSQL_ASSOC );
	$session['leadername'] = $row['leadername'];
	$session['phase'] = $row['phase'];
	$session['ninzuu'] = $row['ninzuu'];
	$session['ninzuu_max'] = $row['ninzuu_max'];
	$session['maisuu'] = $row['maisuu'];
	$session['change_quant'] = $row['change_quant'];
	$session['change_amount'] = $row['change_amount'];
	
	#参加者情報を読み込む
	$sql = "SELECT * FROM members;";
	$query = mysql_query( $sql, $link );
	while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
		$username = $row['username'];
		array_push($members, $username);
		$stock[$username] = $row['stock'];
		$changerest[$username] = $row['changerest'];
		$change_amount[$username] = $row['change_amount'];
	}

	#データベースを切断
	mysql_close( $link );
}

function numlist2sentence( $numlist ) {
	if ( count($words) == 0 ) {
		load_words_table();
	}
	$listwords = array();
	foreach ($numlist as $num) {
		$sent = $words[$num];
		array_push( $listwords, $sent );
	}
	return join(" ", $listwords);
}

function supply_stock {
	load_words_table();
	
	$wordnumber = get_availablewordlist();
	
	#札を配る
	foreach ($members as $memb) {
		$stock[$memb] = join(",", array_splice( $wordnumber,0,$session['maisuu'] ) );
	}
}

#使用可能な単語のリストを得る
#要load_words_table
function get_availablewordlist {
	$usedlist = array();
	
	#使われている札の番号の配列を得る
	foreach ($members as $memb) {
		array_push($usedlist, split(",", $stock[$memb] ) );
	}

	#データベースに接続
	$link = connect_db();

	#投稿されている中に使用された札リストを得る
	$sql = "SELECT wordlist FROM kaitou;"
	$query = mysql_query( $sql, $link );
	while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
		array_push( $usedlist,split(",", $row[0]) );
	}

	#データベースを切断
	mysql_close( $link );
	
	#usedlistを除いた札番号の配列を得る
	$wordnumber = array();
	for ( $i = 0; $i < $totalwords; $i++ ) {
		if ( in_array( $i, $usedlist ) == FALSE) {
			array_push( $wordnumber, $i );
		}
	}

	#シャッフルする
	shuffle($wordnumber);

	return $wordnumber;
}

function commit_mention($mlad,$inmsg) {
	// OAuthオブジェクト生成
	$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);
	
	# 投稿
	$notify_msg = "\@$mlad $inmsg";	
	$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>$notify_msg));	
	return $req;
}

function is_member($name) {
	if ( in_array($name, $members) ) {
		return TRUE;
	}
	return FALSE;
}

function store_session_table {
	#データベースに接続
	$link = connect_db();
	
	#セッション情報をクリアする
	$sql = "DELETE FROM session;";
	$query = mysql_query( $sql, $link );
	
	#セッション情報を書き込む
	$sql = "INSERT INTO session VALUES(
	'$session['leadername']',
	'',
	'$phase',
	$session['ninzuu'],
	$session['ninzuu_max'],
	$session['maisuu'],
	$session['change_quant'],
	$session['change_amount']
	);";
	$query = mysql_query( $sql, $link );

	#参加者情報情報をクリアする
	$sql = "DELETE FROM members;";
	$query = mysql_query( $sql, $link );
	
	#参加者情報を書き込む
	foreach ($members as $memb) {
		$sql = "INSERT INTO members VALUES(
		'$memb',
		'$stock[$memb]',
		$changerest[$memb],
		$change_amount[$memb]
		);";
		$query = mysql_query( $sql, $link );
	}
	
	#データベースを切断
	mysql_close( $link );
}

function load_words_table {
	if ( count($words) == 0 ) {
		#データベースに接続
		$link = connect_db();
		
		#単語を読み込む
		$sql = "SELECT word FROM words;";
		$query = mysql_query( $sql, $link );
		$words = array();
		while ( $row = mysql_fetch_array($query, MYSQL_NUM) ) {
			array_push( $words, $row[0] );
		}
		$totalwords = count($words);
		
		#今日追加された単語数を取得する
		$sql = "SELECT word FROM words WHERE TO_DAYS( NOW() ) = TO_DAYS( date );";
		$query = mysql_query( $sql, $link );
		$todaywords = mysql_num_rows( $query );

		#昨日追加された単語数を取得する
		$sql = "SELECT word FROM words WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1;";
		$query = mysql_query( $sql, $link );
		$yesterdaywords = mysql_num_rows( $query );

		#データベースを切断
		mysql_close( $link );
	}
}

function refresh_kaitou_table {
	my($numlogs,$oldnum);

	#データベースに接続
	$link = connect_db();
	
	if ( is_exist_table( $link, "kaitou" ) ) {
		#過去ログのファイル名をひとつずつ送る
		for ($numlogs=0; is_exist_table($link, "kaitou_$numlogs"); $numlogs++) {}
		for (; $numlogs>0; $numlogs--) {
			$oldnum = $numlogs-1;
			$sql = "ALTER TABLE kaitou_$oldnum RENAME TO kaitou_$numlogs;";
			$query = mysql_query( $sql, $link );
		}
		$sql = "ALTER TABLE kaitou RENAME TO kaitou_0;";
		$query = mysql_query( $sql, $link );
	}
	
	#データベースを切断
	mysql_close( $link );
}
