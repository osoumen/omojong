<?php
//ヘッダー
//$pagetitle = '参加リスト';
$pagetitle = '';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<?php
$myname = $_SESSION['access_token']['screen_name'];
for ($i=0; $i<count($session_key_list); $i++) {
	echo '<a href="' . $g_script . '?p=' . $session_key_list[$i] . '">';
	
	$caption = '';
	if ( $phase_list[$i] == 'sanka' ) {
		$caption = '参加募集中';
	}
	if ( $phase_list[$i] == 'toukou' ) {
		$caption = '作成中';
	}
	if ( $phase_list[$i] != 'kekka' ) {
		write_members_only_html($memberlist_list[$i], $myname, NULL, $caption);
	}

	echo '</a>';
}

echo '<h1>最近始めたユーザー</h1>';
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
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">今までの結果を見る</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
