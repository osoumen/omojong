<?php
require_once 'globals.php';
require_once 'common.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="<?php echo $g_css_url; ?>" type="text/css" />
<title>Twitterおもじゃん</title>
</head>
<body>
<center>
<?php
$smarty->display( 'tpl/header.tpl' );
?>
<hr>
<h3>最初から始める</h3>
進行中のゲームを中断して最初からやり直しますか？<br>
<a href="page_start.php?p=<?php echo $_REQUEST['p'] ?>" target=_top>[ＯＫ]</a><br>
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
</center>
<?php
$smarty->display( 'tpl/footer.tpl' );
?>
</body>
</html>