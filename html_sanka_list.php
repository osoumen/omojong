<?php
//ヘッダー
//$pagetitle = '参加リスト';
$pagetitle = '';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_left">
<?php
$myname = htmlspecialchars( $_SESSION['access_token']['screen_name'] );

if ( count($session_key_list) > 0 ) {
	echo '<div class="general_container">';
	echo "<h2>$myname さんが参加中</h2>";
	for ($i=0; $i<count($session_key_list); $i++) {
		echo '<a href="' . $g_script . '?p=' . $session_key_list[$i] . '">';
		
		$caption = '';
		if ( $phase_list[$i] == 'toukou' ) {
			$caption = '解答中';
		}
		if ( $phase_list[$i] == 'kekka' ) {
			$caption = '終了';
		}
		write_members_only_html($memberlist_list[$i], $myname, $caption);
	
		echo '</a>';
	}
	echo '</div>';
}
else {
	echo '<div class="general_container">';
	echo '<a href="page_start.php"><h2>つぶメモナイズする</h2></a>';
	echo '</div>';
}
echo '<div class="member">';
echo '<h4>最近始めたユーザー</h4>';
echo '<ul>';
$disclosed_session_key = get_disclosed_session_key( $link );
foreach ( $disclosed_session_key as $key => $value ) {
	$url = sprintf( "%s?%s=%d", $g_script, $gameid_param_name, $value );
	echo '<li><a href="' . $url . '">';
	echo $key;
	echo 'さん</a></li>';
}
echo '</ul>';
echo '</div>';
?>
</div>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">みんながつぶメモナイズした文を見る</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
