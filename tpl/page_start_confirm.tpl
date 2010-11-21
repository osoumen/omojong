<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<title>Twitterおもじゃん:新しく始める:確認</title>
</head>
<body>
<center>
{include file={$header_path}}

<hr>
<h3>新しく開始</h3>
以下の条件で始めますか？<br>
<table>
<tr><td><b>Username</b></td><td>$in{'username'}</td></tr>
<tr><td><b>人数</b></td><td>$in{'ninzuu'}〜$in{'ninzuu_max'}</td></tr>
<tr><td><b>枚数</b></td><td>$in{'maisuu'}</td></tr>
<tr><td><b>札の交換</b></td><td>$in{'change_quant'}回まで$in{'change_amount'}枚以内
</td></tr>
</table>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="0">
<input type="hidden" name="username" value="$in{'username'}">
<input type="hidden" name="ninzuu" value="$in{'ninzuu'}">
<input type="hidden" name="ninzuu_max" value="$in{'ninzuu_max'}">
<input type="hidden" name="maisuu" value="$in{'maisuu'}">
<input type="hidden" name="change_quant" value="$in{'change_quant'}">
<input type="hidden" name="change_amount" value="$in{'change_amount'}">
<input type="submit" name="submit" value="ＯＫ">
</form>
<a href="$g_script" target=_top>[戻る]</a>

</center>
{include file={$footer_path}}
</body>
</html>