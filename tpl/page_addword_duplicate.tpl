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
<h3>“{$inword}”は既にありますが追加しますか？</h3>
<form action="$page_addword.php" method="post">
<input type="hidden" name="word" value="{$inword}">
<input type="hidden" name="forceadd" value="1">
<input type="submit" name="submit" value="追加する"><br>
</form>
<a href="{$g_script}" target=_top>[やめる]</a>
</center>
{include file={$footer_path}}
</body>
</html>