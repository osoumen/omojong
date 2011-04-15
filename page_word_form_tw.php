<?php

require_once 'globals.php';
require_once 'common.php';
require_once 'twitteroauth.php';

dl('mecab.so');

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

//ログインしてなかったらtopに飛ぶ
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

$totalwords = add_word_from_twitter( $link, $words_table_name );

$pagetitle = '単語の追加';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->assign( 'totalwords', $totalwords );
$smarty->display( $g_tpl_path . 'page_word_from_tw.tpl' );

//データベースを切断
mysql_close( $link );
