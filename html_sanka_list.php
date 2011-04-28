<?php
//ヘッダー
//$pagetitle = '参加リスト';
$pagetitle = '';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<h1>以下に参加中</h1>
<?php
for ($i=0; $i<count($session_key_list); $i++) {
	echo '<a href="' . $g_script . '?p=' . $session_key_list[$i] . '">';
	echo $phase_list[$i] . '<br />';
	foreach ( $memberlist_list[$i] as $memb ) {
		echo $memb . '<br />';
	}
	echo '</a><br />';
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
