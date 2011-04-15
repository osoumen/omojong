<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

if ( isset( $in['num'] ) ) {
	$num = $in['num'];
}
else {
	$num = $session['latest_log'];
}

$nextlog = $num-1;
$prevlog = $num+1;
$exist_next = is_exist_table($link, sprintf('%s_%d', $kaitou_table_name, $nextlog) );
$exist_prev = is_exist_table($link, sprintf('%s_%d', $kaitou_table_name, $prevlog) );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん:過去の記録</title>
</head>
<body>
<center>
<?php
//ヘッダー
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<?php
if ( $exist_next ) {
	echo '<a href="page_pastlog.php?p=' . $session['session_key'] . '&num='.$nextlog.'">[←もっと古い記録] </a>';
}
if ( $exist_prev && ($prevlog >= 0) ) {
	echo '<a href="page_pastlog.php?p=' . $session['session_key'] . '&num='.$prevlog.'">[もっと新しい記録→] </a>';
}
?>
<br>
<hr>
<?php
//結果表示
$kekka_table = sprintf( "%s_%d", $kaitou_table_name, $num );

if ( $num == 0 ) {
	echo '<h2>前回の結果</h2><br>';
}
else {
	echo '<h2>過去の結果 '.$num.'</h2><br>';
}

if ( is_exist_table( $link, $kekka_table ) == FALSE ) {
	error( 'データが存在しません。' );
}
$sql = "SELECT id,content,author,votes FROM $kekka_table ORDER BY votes DESC";
$query = mysql_query( $sql, $link );

while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$ansindex = $row[0];
	$sentence = $row[1];
	$kaitousya = $row[2];
	$hyousuu = $row[3];
	$tweet_msg = urlencode(' 『' . $sentence . '』by @' . $kaitousya . ' ');
	
	$smarty->assign( 'pastno', $num );
	$smarty->assign( 'session_key', $session['session_key'] );
	$smarty->assign( 'ansindex', $ansindex );
	$smarty->assign( 'sentence', $sentence );
	$smarty->assign( 'kaitousya', $kaitousya );
	$smarty->assign( 'hyousuu', $hyousuu );
	$smarty->assign( 'tweet_msg', $tweet_msg );
	$smarty->display( $g_tpl_path . 'html_kekka_past.tpl' );
}
mysql_close( $link );
?>
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
</center>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>