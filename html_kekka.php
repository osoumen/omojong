<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $pastlog_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

load_members( $link, $members, $stock, $changerest, $change_amount );

//参加者名を取得
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//結果ページのnumを求める
$num = explode( '_', $kaitou_table_name );
$num = $num[1];

//ヘッダー
$pagetitle = '結果';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );

//参加者一覧表示
write_members_html( $members, $stock, $c_username );

echo '<div id="content_main">';

//結果表示
/*
if ( $g_kekkasort ) {
	$sql = "SELECT id,content,author,votes FROM $kaitou_table_name ORDER BY votes DESC";
}
else {
	$sql = "SELECT id,content,author,votes FROM $kaitou_table_name";
}
$query = mysql_query( $sql, $link );
while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$ansindex = $row[0];
	$sentence = $row[1];
	$kaitousya = $row[2];
	$hyousuu = $row[3];
	$tweet_msg = urlencode(' 『' . $sentence . '』by @' . $kaitousya . ' ');
	
	$smarty->assign( 'session_key', $session['session_key'] );
	$smarty->assign( 'ansindex', $ansindex );
	$smarty->assign( 'sentence', $sentence );
	$smarty->assign( 'kaitousya', $kaitousya );
	$smarty->assign( 'hyousuu', $hyousuu );
	$smarty->assign( 'tweet_msg', $tweet_msg );
	$smarty->display( $g_tpl_path . 'html_kekka.tpl' );
}
*/

echo '<a href="page_pastlog.php?num=' . $num . '"><span class="kekka_btn">結果を見る</span></a><br />';

if ( $allow_addword ) {
	$words = array();
	$totalwords = load_words_table( $link, $words );
	$todaywords = get_todaywords( $link );
	$yesterdaywords = get_yesterdaywords( $link );

	//単語を追加フォーム
	$smarty->assign( 'totalwords', $totalwords );
	$smarty->assign( 'todaywords', $todaywords );
	$smarty->assign( 'yesterdaywords', $yesterdaywords );
	$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
}
echo '</div>';

echo '<div id="pre_footer">';
echo '<a href="page_start.php?p=' . $session['session_key'] . '">[始めからやる]</a>';

//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php">[過去ログ]</a>';
}

echo '</div>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
