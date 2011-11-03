<?php
//ヘッダー
$pagetitle = '';
$smarty->assign( 'pagetitle', $pagetitle );
$g_js_url[] = 'http://widgets.twimg.com/j/2/widget.js';
$smarty->assign( 'g_js_url', $g_js_url );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<div class="general_container">
あなたもしくはフォロワーの誰かがつぶやいたかもしれない言葉を使って、面白い文章を作って下さい。
</div>
<div class="general_container">
<h3>始めるには</h3>
<div class="login_link">
<a href="twitter_request.php">Twitterでログイン</a>
</div>
</div>
<div class="general_container">
<h3>みんなが作成したコピー</h3>
</div>
<?php
//最新の過去ログ値を取得する
$latest_pastlog = get_latest_pastlog_no( $link );
if ( $latest_pastlog > 0 ) {
	//投稿用トークン生成
	$post_token = generate_post_token();
	$_SESSION['post_token'] = $post_token;
	
	$kekka_table = sprintf( "%s_%d", $pastlog_table_name, $latest_pastlog );
	$sql = "SELECT id,content,author,votes,date FROM $kekka_table ORDER BY votes DESC";
	$query = mysql_query( $sql, $link );
	while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
		$ansindex = $row[0];
		$sentence = $row[1];
		$kaitousya = $row[2];
		$hyousuu = $row[3];
		$date = $row[4];
		$wj_search = sprintf( '%s?%s=%d#%d%s', $g_scripturl, $pastlog_param_name, $latest_pastlog, $ansindex, $hash_tag );
		$tweet_msg = ' ＜' . $sentence . '＞ ' . $wj_search;
		
		$smarty->assign( 'pastno', $latest_pastlog );
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
}
?>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">みんなの作成したコピーをさらに見る</a>
</div>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
