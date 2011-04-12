<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん</title>
</head>
<body>
<center>
<?php
//ヘッダー
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<hr>
<a href="twitter_request.php">[twitterにログイン]</a><br>
<br>
</center>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
?>
</body>
</html>