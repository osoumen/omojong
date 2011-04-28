<?php
//ヘッダー
$pagetitle = 'TOP';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<a class="login_link" href="twitter_request.php">twitterでログイン</a><br />
<div id="recent_started">
<h1>最近始めたユーザー</h1>
<?php
$disclosed_session_key = get_disclosed_session_key( $link );
foreach ( $disclosed_session_key as $key => $value ) {
	$url = sprintf( "%s?%s=%d", $g_script, $gameid_param_name, $value );
	echo '<a href="' . $url . '">';
	echo $key;
	echo '</a><br />';
}
?>
</div>
</div>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">[過去ログ]</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
