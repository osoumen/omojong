<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$session = array();
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

$in = array_merge( $_POST, $_GET );

//--エラーチェック--
if ( $session['phase'] != 'kekka') {
	error("現在投票を受け付けていません。");
}
if ( ctype_digit( $in['ansnum'] ) == FALSE ) {
	error("投票は数値を指定してください。");
}

//解答ファイル中の得票数をインクリメントする
$sql = sprintf( "UPDATE %s SET votes = votes + %d WHERE id = %d", $kaitou_table_name, $in{'increment'}, $in{'ansnum'} );
$query = mysql_query( $sql, $link );
if ( !$query ) {
	error("範囲外の解答を指定しています。");
}

//投票した解答を得る
$sql = sprintf( "SELECT content FROM %s WHERE id = %d", $kaitou_table_name, $in{'ansnum'} );
$query = mysql_query( $sql, $link );
while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
	$sentence = $row[0];
}

//データベースを切断
mysql_close( $link );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん:参加</title>
</head>
<body>
<center>
<?php
$smarty->display( 'tpl/header.tpl' );
?>
<hr>
<h3><?php echo $sentence; ?>に投票しました</h3>
投票ありがとうございます。<br>
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
</center>
<?php
$smarty->display( 'tpl/footer.tpl' );
?>
</body>
</html>