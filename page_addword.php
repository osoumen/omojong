<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

//データベースに接続
$link = connect_db();

$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

if ( mb_strlen( $in['word'] ) == 0 ) {
	error("文字が入っていないぞ？");
}
$newword = $in['word'];

//以前に同じ単語が入れられていないかチェック
if ( isset( $in['forceadd'] ) == FALSE ) {
	$sql = sprintf( "SELECT word FROM %s WHERE word = '%s'", $words_table_name, $newword );
	$query = mysql_query( $sql, $link );
	$found = mysql_num_rows( $query );
	if ( $found > 0 ) {
		$smarty->assign( 'inword', $in['word'] );
		$smarty->display( $g_tpl_path . 'page_addword_duplicate.tpl' );
		//データベースを切断
		mysql_close( $link );
		exit();
	}
}

//単語をデータベースに書き込む
$sql = sprintf( "INSERT INTO %s (word, date) VALUES ('%s', NOW())", $words_table_name, $newword );
$query = mysql_query( $sql, $link );

//最大保持数を超えたら古い順に削除する
//	while (((@filedata-1)>$g_maxwords) and ($g_maxwords ne 0)) {
//		splice(@filedata,1,1);
//	}

$smarty->assign( 'inword', $in['word'] );
$smarty->display( $g_tpl_path . 'page_addword.tpl' );

//データベースを切断
mysql_close( $link );
