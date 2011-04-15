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
<title>Twitterおもじゃん:解答受付中</title>
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
<h3>解答して下さい</h3>
<table border=0>
<tr><th>参加者</th><th>解答状況</th></tr>
<?php
//参加者一覧表示
foreach ( $members as $memb ) {
	if ($memb === $c_username) {
		$nametext = "<font size=+1><b>$memb</b></font>";
	}
	else {
		$nametext = $memb;
	}
	if ($stock[$memb] === '') {
		echo "<tr><td>$nametext さん</td><td><font color=blue>解答終了</font></td></tr>\n";
	}
	else {
		echo "<tr><td>$nametext さん</td><td><font color=red>解答中</font></td></tr>\n";
	}
}
?>
</table><br>
<?php
if ( in_array($c_username, $members) ) {
	//参加者である
	
	//解答したものを表示
	$sql = sprintf( "SELECT content FROM %s WHERE author = '%s'", $kaitou_table_name, $c_username );
	$query = mysql_query( $sql, $link );
	$answers = array();
	while ( $row = @mysql_fetch_array( $query, MYSQL_NUM ) ) {
		array_push( $answers, $row[0] );
	}
	$smarty->assign( 'c_username', $c_username );
	$smarty->assign( 'answers', $answers );
	$smarty->display( $g_tpl_path . 'html_answers.tpl' );
	
	if ( $stock[$c_username] !== '' ) {
		//持ち札の一覧を表示
		$stock_array = explode( ',', $stock[$c_username] );
		$word_array = array();
		foreach ( $stock_array as $num ) {
			array_push( $word_array, $words[$num] );
		}
		$smarty->assign( 'stock_array', $stock_array );
		$smarty->assign( 'word_array', $word_array );
		$smarty->display( $g_tpl_path . 'html_stocks.tpl' );
		
		//投稿フォームを表示
		$smarty->display( $g_tpl_path . 'html_answer_form.tpl' );
		
		//交換回数が残っている
		if ( $changerest[$c_username] > 0 && $change_amount[$c_username] ) {
			$smarty->assign( 'c_rest', $changerest[$c_username] );
			$smarty->assign( 'c_amount', $change_amount[$c_username] );
			$smarty->display( $g_tpl_path . 'html_change_form.tpl' );
		}
		
		//解答終了ボタンを表示
		$smarty->display( $g_tpl_path . 'html_giveup_button.tpl' );
		
		
	}
	else {
		echo "$c_username さんはもう解答できません。<br>";
	}
}
else {
	//参加者以外
	if ( count( $members ) < $session['ninzuu_max'] ) {
		echo '<b>途中参加受付中！</b><br>';
		$smarty->display( $g_tpl_path . 'html_sanka_form.tpl' );
	}
}
?>
<br>
<?php
if ( $allow_addword ) {
	//単語を追加フォーム
	$smarty->assign( 'totalwords', $totalwords );
	$smarty->assign( 'todaywords', $todaywords );
	$smarty->assign( 'yesterdaywords', $yesterdaywords );
	$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
}

//このページへのリンク
echo '<a href="' . $g_script . '?p=' . $session['session_key'] . '">[このページへのリンク]</a><br>';
?>
</center>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>