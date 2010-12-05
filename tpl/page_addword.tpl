<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<title>Twitterおもじゃん:単語の追加</title>
</head>
<body>
<center>
{include file={$header_path}}
<hr>
<h3>“{$inword}”を追加しました</h3>
<br>
<form action="$page_addword.php" method="post">
さらに追加
<input type="text" name="word" value="">
<input type="submit" name="submit" value="追加"><br>
</form>
<a href="{$g_script}" target=_top>[戻る]</a>
</center>
{include file={$footer_path}}
</body>
</html>