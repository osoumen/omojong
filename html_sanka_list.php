<?php
//ヘッダー
$pagetitle = '参加リスト';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
以下のゲームに参加中<br>
<?php
for ($i=0; $i<count($session_key_list); $i++) {
	echo '<a href="' . $g_script . '?p=' . $session_key_list[$i] . '">';
	echo $phase_list[$i] . '<br>';
	foreach ( $memberlist_list[$i] as $memb ) {
		echo $memb . '<br>';
	}
	echo '</a><br>';
}
?>
<br>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
