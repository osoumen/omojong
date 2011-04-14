<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $kaitou_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

$words = array();
$totalwords = load_words_table( $link, $words );
$todaywords = get_todaywords( $link );
$yesterdaywords = get_yesterdaywords( $link );

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
load_members( $link, $members, $stock, $changerest, $change_amount );

//参加者名を取得
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん:参加募集中</title>
</head>
<body>
<center>
<?php
//ヘッダー
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<?php
//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php?p=' . $session['session_key'] . '">[過去の記録]</a><hr>';
}
?>
<h2>参加募集中</h2>
<table border=0>
<tr><th>参加者</th></tr>
<?php
//参加者一覧表示
foreach ( $members as $memb ) {
	if ($memb === $c_username) {
		$nametext = "<font size=+1><b>$memb</b></font>";
	}
	else {
		$nametext = $memb;
	}
	echo "<tr><td>$nametext さん</td></tr>";
}
?>
</table><br>
<?php
//残り参加人数表示
$rest = $session['ninzuu'] - count( $members );
echo "あと$rest 人の参加が必要です。<br><br>";

if ( in_array($c_username, $members) ) {
	if ( $c_username !== $session['leadername'] ) {
		$smarty->display( $g_tpl_path . 'html_sanka_cancel_btn.tpl' );
	}
}
else {
	//参加者以外の場合
	//参加表明フォームを表示
	$smarty->display( $g_tpl_path . 'html_sanka_form.tpl' );
}
?>
<br>
<?php
//単語を追加フォーム
$smarty->assign( 'totalwords', $totalwords );
$smarty->assign( 'todaywords', $todaywords );
$smarty->assign( 'yesterdaywords', $yesterdaywords );
$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
?>
</center>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>