<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$in = $_REQUEST;

//無入力はエラー
if ( empty($in[$gameid_param_name]) || empty($in['answer']) ) {
	echo 'error!';
	exit;
}

//単語リストのテーブル名を読み込む
$sql = sprintf( "SELECT words_table_name FROM session WHERE session_key = %s", $in[$gameid_param_name] );
$query = mysql_query( $sql, $link );
if ( !$query || mysql_num_rows( $query ) == 0 ) {
	echo 'error!';
	exit;
}
$row = @mysql_fetch_array( $query, MYSQL_ASSOC );
$words_table_name = $row['words_table_name'];

$anslist = explode( ',', $in['answer'] );
//数字以外が入ってないか
foreach ( $anslist as $ansnum ) {
	if ( ctype_digit( $ansnum ) == FALSE ) {
		echo 'error!';
		exit;
	}
}

//存在しない札を入力していないか
$words = array();
$totalwords = load_words_table( $link, $words );
foreach ( $anslist as $ansnum ) {
	if ( $ansnum >= $totalwords ) {
		echo 'error!';
		exit;
	}
}

$sentence = numlist2sentence( $anslist, $words );

echo $sentence;