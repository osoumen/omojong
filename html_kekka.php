<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $pastlog_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

$words = array();
$totalwords = load_words_table( $link, $words );
$todaywords = get_todaywords( $link );
$yesterdaywords = get_yesterdaywords( $link );

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
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
?>
<hr>
<?php
//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php">[過去ログ]</a><hr>';
}
//echo '<h2>結果発表</h2><br>';
?>
<table border=0>
<tr><th>参加者</th><th>解答状況</th></tr>
<?php
//参加者一覧表示
foreach ( $members as $memb ) {
	if ($memb === $c_username) {
		$nametext = "<font size=+1><b>$memb</b></font>";
	}
	else {
		$nametext = $memb;
	}
	if ($stock[$memb] === '') {
		echo "<tr><td>$nametext さん</td><td><font color=blue>解答終了</font></td></tr>\n";
	}
	else {
		echo "<tr><td>$nametext さん</td><td><font color=red>解答中</font></td></tr>\n";
	}
}

echo '</table><br><a href="page_pastlog.php?num=' . $num . '">[結果を見る]</a><br><hr>';

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

if ( $is_login ) {
	echo '<a href="page_start.php?p=' . $session['session_key'] . '">[始めからやる]</a><br><br>';
}
else {
	echo '<a href="twitter_request.php">[twitterにログイン]</a><br><br>';
}

if ( $allow_addword ) {
	//単語を追加フォーム
	$smarty->assign( 'totalwords', $totalwords );
	$smarty->assign( 'todaywords', $todaywords );
	$smarty->assign( 'yesterdaywords', $yesterdaywords );
	$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
}

//このページへのリンク
//echo '<a href="' . $g_script . '?p=' . $session['session_key'] . '">[このページへのリンク]</a><br>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
