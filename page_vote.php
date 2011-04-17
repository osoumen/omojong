<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$session = array();

$in = array_merge( $_POST, $_GET );

//過去データへの投票の場合
if ( isset($in['num']) ) {
	$kaitou_table_name = $pastlog_table_name . '_' . $in['num'];
}
//結果発表中のデータへの投票の場合
/*
elseif ( isset($in[$gameid_param_name]) ) {
	$session = load_session_table( $link );
}
*/
else {
	error("対象が指定されていません。");
}

//--エラーチェック--
if ( ctype_digit( $in['ansnum'] ) == FALSE ) {
	error("投票は数値を指定してください。");
}

//解答ファイル中の得票数をインクリメントする
$sql = sprintf( "UPDATE %s SET votes = votes + %d WHERE id = %d", $kaitou_table_name, $in{'increment'}, $in{'ansnum'} );
$query = mysql_query( $sql, $link );
if ( !$query ) {
	error("範囲外の解答を指定しています。");
}

//投票した解答を得る
$sql = sprintf( "SELECT content,author,votes FROM %s WHERE id = %d", $kaitou_table_name, $in{'ansnum'} );
$query = mysql_query( $sql, $link );
while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$sentence = $row[0];
	$author = $row[1];
	$votes = $row[2];
}

//HOT機能(一定数の得票数の作品を自動でツイートする)
if ( $votes == $g_hot_votes ) {
	$pre = 'ＨＯＴ：';
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'page_pastlog.php?num=' . $in['num'];
	$url = " http://$host$uri/$extra";
	
	//合計文字数140文字をオーバーしていたら本文を縮める
	$max_len = 140 - mb_strlen( $pre . $url );
	$sentence = mb_strimwidth( $sentence, 0, $max_len, '…' );
	$msg = $pre . $sentence . $url;
	
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);
	$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>$msg));
}

$pagetitle = '参加';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( 'tpl/header.tpl' );
?>
<h3><?php echo $sentence; ?></h3>
に投票しました。<br>
<a href="page_pastlog.php?num=<?php echo $in['num']; ?>" target=_top>[戻る]</a>
<?php
$smarty->display( 'tpl/footer.tpl' );
