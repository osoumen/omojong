<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $kaitou_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

$words = array();
$totalwords = load_words_table( $link, $words );
$todaywords = get_todaywords( $link );
$yesterdaywords = get_yesterdaywords( $link );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん:結果</title>
</head>
<body>
<center>
<?php
//ヘッダー
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<?php
//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php?p=' . $session['session_key'] . '">[過去の記録]</a><hr>';
}
echo '<h2>結果発表</h2><br>';
//結果表示
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

if ( $is_login ) {
	echo '<a href="page_start.php?p=' . $session['session_key'] . '">[新しく始める]</a><br><br>';
}
else {
	echo '<a href="twitter_request.php">[twitterにログイン]</a><br><br>';
}

//単語を追加フォーム
$smarty->assign( 'totalwords', $totalwords );
$smarty->assign( 'todaywords', $todaywords );
$smarty->assign( 'yesterdaywords', $yesterdaywords );
$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
?>
</center>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>