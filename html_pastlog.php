<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

//最新の過去ログ値を取得する
$sql = sprintf( "SELECT * FROM global" );
$query = mysql_query( $sql, $link );
while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
	$latest_pastlog = $row['latest_pastlog'];
}

if ( $in[$pastlog_param_name] == 'new' ) {
	$num = $latest_pastlog;
}
else {
	$num = $in[$pastlog_param_name];
}

if ( $latest_pastlog < 0 ) {
	error( 'ログがまだありません。');
}

//ヘッダー
$pagetitle = 'これまでの模様';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );

//過去ログナビゲーション
write_pastlog_nav( $link, $num, $pastlog_table_name );

//結果表示
$kekka_table = sprintf( "%s_%d", $pastlog_table_name, $num );

//投稿用トークン生成
$seed = $_SERVER['REMOTE_ADDR'] . date('c');
$post_token = hash('ripemd160', $seed);
$_SESSION['post_token'] = $post_token;

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
	$wj_search = sprintf( '%s?%s=%d#%d%s', $g_scripturl, $pastlog_param_name, $num, $ansindex, $hash_tag );
//	$tweet_msg = urlencode(' ＜' . $sentence . '＞ ' . $wj_search );
	$tweet_msg = ' ＜' . $sentence . '＞ ' . $wj_search;
	
	$smarty->assign( 'pastno', $num );
	$smarty->assign( 'ansindex', $ansindex );
	$smarty->assign( 'sentence', $sentence );
	$smarty->assign( 'kaitousya', $kaitousya );
	$smarty->assign( 'hyousuu', $hyousuu );
	$smarty->assign( 'tweet_msg', $tweet_msg );
	$smarty->assign( 'date', $date );
	$smarty->assign( 'wj_search', $wj_search );
	$smarty->assign( 'post_token', $post_token );
	$smarty->display( $g_tpl_path . 'html_kekka_past.tpl' );
}
?>
<div id="pre_footer">
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
