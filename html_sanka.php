<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $pastlog_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

load_members( $link, $members, $stock, $changerest, $change_amount );

//参加者名を取得
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//ヘッダー
//$pagetitle = '参加募集中';
$pagetitle = $c_username;
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );

//参加者一覧表示
write_members_only_html( $members, $stock, $c_username, $session );

echo '<div id="content_right">';

if ( $allow_addword ) {
	$words = array();
	$totalwords = load_words_table( $link, $words );
	$todaywords = get_todaywords( $link );
	$yesterdaywords = get_yesterdaywords( $link );

	//単語を追加フォーム
	$smarty->assign( 'totalwords', $totalwords );
	$smarty->assign( 'todaywords', $todaywords );
	$smarty->assign( 'yesterdaywords', $yesterdaywords );
	$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
}

echo '</div>';
echo '<div id="pre_footer">';

if ( $c_username == $session['leadername'] ) {
	echo '<a href="page_start_confirm.php?p=' . $session['session_key'] . '">[始めからやる]</a>';
}

//このページへのリンク
write_urltweet( $g_scripturl, $session['session_key'] );

//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="'.$g_script.'?'.$pastlog_param_name.'=new">これまでの模様</a>';
}

echo '</div>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
