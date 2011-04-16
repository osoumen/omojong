<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

//データベースに接続
$link = connect_db();

//最新の過去ログ値を取得する
$sql = sprintf( "SELECT * FROM global" );
$query = mysql_query( $sql, $link );
while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
	$latest_pastlog = $row['latest_pastlog'];
}

if ( isset( $in['num'] ) ) {
	$num = $in['num'];
}
else {
	$num = $latest_pastlog;
}

if ( $latest_pastlog < 0 ) {
	error( '過去ログがまだありません。');
}

$nextlog = $num-1;
$prevlog = $num+1;
$exist_next = is_exist_table($link, sprintf('%s_%d', $pastlog_table_name, $nextlog) );
$exist_prev = is_exist_table($link, sprintf('%s_%d', $pastlog_table_name, $prevlog) );

//ヘッダー
$pagetitle = '過去ログ';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<?php
if ( $exist_next ) {
	echo '<a href="page_pastlog.php?num=' . $nextlog.'">[←もっと古い記録] </a>';
}
if ( $exist_prev && ($prevlog >= 0) ) {
	echo '<a href="page_pastlog.php?num=' . $prevlog.'">[もっと新しい記録→] </a>';
}
?>
<br>
<hr>
<?php
//結果表示
$kekka_table = sprintf( "%s_%d", $pastlog_table_name, $num );

/*
if ( $num == 0 ) {
	echo '<h2>前回の結果</h2><br>';
}
else {
	echo '<h2>過去の結果 '.$num.'</h2><br>';
}
*/
if ( is_exist_table( $link, $kekka_table ) == FALSE ) {
	error( 'データが存在しません。' );
}
if ( $g_kekkasort ) {
	$sql = "SELECT id,content,author,votes,date FROM $kekka_table ORDER BY votes DESC";
}
else {
	$sql = "SELECT id,content,author,votes,date FROM $kekka_table";
}
$query = mysql_query( $sql, $link );

while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$ansindex = $row[0];
	$sentence = $row[1];
	$kaitousya = $row[2];
	$hyousuu = $row[3];
	$date = $row[4];
	$tweet_msg = urlencode(' 『' . $sentence . '』by @' . $kaitousya . ' ');
	
	$smarty->assign( 'pastno', $num );
	//$smarty->assign( 'session_key', $session['session_key'] );
	$smarty->assign( 'ansindex', $ansindex );
	$smarty->assign( 'sentence', $sentence );
	$smarty->assign( 'kaitousya', $kaitousya );
	$smarty->assign( 'hyousuu', $hyousuu );
	$smarty->assign( 'tweet_msg', $tweet_msg );
	$smarty->assign( 'date', $date );
	$smarty->display( $g_tpl_path . 'html_kekka_past.tpl' );
}
mysql_close( $link );
?>
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
