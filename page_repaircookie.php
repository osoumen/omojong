<?php

require_once 'globals.php';
require_once 'common.php';

$in = array_merge( $_POST, $_GET );

//データベースに接続
$link = connect_db();

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
load_members( $link, $members, $stock, $changerest, $change_amount );

//データベースを切断
mysql_close( $link );

if ( isset($in['username']) ) {
	if ( in_array($in['username'], $members) == FALSE ) {
		error( $in['username'] . 'さんは参加していません。' );
	}
	//クッキーを発行
	$c_username = $in['username'];
	setcookie( 'username', $in['username'], time() + 3600 * 24 * 75, '/' );	//75日有効
	
	message( 'クッキーの再発行', 'クッキーが再発行されました。' );	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん:エラー</title>
</head>
<body>
<center>
<?php
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<h3>クッキーの再発行</h3>
Usernameを入力してください。<br>
<form action='page_repaircookie.php' method='post'>
<input type="hidden" name="mode" value="repaircookie">
Username：<input type="text" name="username" value="">
<input type="submit" name="submit" value="再発行"><br>
</form>
<?php
echo "<a href='$g_script' target=_top>[戻る]</a>";
?>
<hr>
</center>
<?php
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>