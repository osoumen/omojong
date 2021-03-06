<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$session = array();

$in = array_merge( $_POST, $_GET );


//--エラーチェック--
if ( ctype_digit( $in['ansnum'] ) == FALSE ) {
	error("送信内容が不正です。");
}
if ( ctype_digit( $in[$pastlog_param_name] ) == FALSE ) {
	error("送信内容が不正です。");
}

//過去データへの投票の場合
if ( isset($in[$pastlog_param_name]) ) {
	$kaitou_table_name = $pastlog_table_name . '_' . $in[$pastlog_param_name];
}
//結果発表中のデータへの投票の場合
else {
	error("対象が指定されていません。");
}

//投票した解答を得る
$sql = sprintf( "SELECT content,author,votes FROM %s WHERE id = %d", $kaitou_table_name, $in['ansnum'] );
$query = mysql_query( $sql, $link );
while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$sentence = $row[0];
	$author = $row[1];
	$votes = $row[2];
}

//解答ファイル中の得票数をインクリメントする
if ( isset( $_SESSION['post_token'] ) && $_SESSION['post_token'] === $in['token'] ) {
	$sql = sprintf( "UPDATE %s SET votes = votes + IF (votes < 999,1,0) WHERE id = %d", $kaitou_table_name, $in['ansnum'] );
	$query = mysql_query( $sql, $link );
	if ( !$query ) {
		error("範囲外の解答を指定しています。");
	}
	
	//HOT機能(一定数の得票数の作品を自動でツイートする)
	if ( $votes == $g_hot_votes ) {
		$pre = 'おみごと！＜';
		$url = '＞ ' . $g_scripturl . '?' .$pastlog_param_name. '=' . $in[$pastlog_param_name] . '#' . $in['ansnum'];
		
		//合計文字数140文字をオーバーしていたら本文を縮める
		$max_len = 140 - mb_strlen( $pre . $url );
		$sentence = mb_strimwidth( $sentence, 0, $max_len, '…' );
		$msg = $pre . $sentence . $url;
		
		// OAuthオブジェクト生成
		$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN,ACCESS_TOKEN_SECRET);
		$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>$msg));
	}
}
unset( $_SESSION['post_token'] );

$pagetitle = '参加';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( 'tpl/header.tpl' );
?>
<div id="content_main">
<div class="general_container">
<h3><?php echo $sentence; ?></h3>
に対して「いいね！」と言いました。<br />
</div>
<div id="pre_footer">
<a href="<?php echo "$g_script?$pastlog_param_name=$in[$pastlog_param_name]"; ?>" target=_top>戻る</a>
</div>
</div>
<?php
$smarty->display( 'tpl/footer.tpl' );
