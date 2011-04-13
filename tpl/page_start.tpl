<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<title>Twitterおもじゃん:新しく始める</title>
</head>
<body>
<center>
{include file={$header_path}}

<hr>
<h3>新しく始める</h3>
<font color=red>{$err_str}</font><br>
<form action="page_start.php" method="post">
<table>
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="{$g_start_confirm}">
<tr><td><b>Username</b></td><td>{$in.username}</td></tr>
<tr>
<td><b>人数</b></td>
<td><input type="text" name="ninzuu" value="{$in.ninzuu}" size=4>〜<input type="text" name="ninzuu_max" value="{$in.ninzuu_max}" size=4>人</td>
</tr>
<tr><td><b>枚数</b></td><td><input type="text" name="maisuu" value="{$in.maisuu}" size=4>枚</td></tr>
<tr><td><b>札の交換</b></td><td><input type="text" name="change_quant" value="{$in.change_quant}" size=4>回まで
<input type="text" name="change_amount" value="{$in.change_amount}" size=4>枚以内
</td></tr>
</table>
<input type="submit" name="submit" value="始める"><br>
</form>
<a href="{$g_script}" target=_top>[戻る]</a>

</center>
{include file={$footer_path}}
</body>
</html>
