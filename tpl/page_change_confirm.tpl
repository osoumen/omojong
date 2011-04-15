<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<title>Twitterおもじゃん:札の交換</title>
</head>
<body>
<center>
{include file={$header_path}}
<hr>
{foreach from="$disp_list" item="value" key="key"}
{$key}：{$value}<br>
{/foreach}
<br>を捨ててもよろしいですか？<br><br>
<form action="page_change.php" method="post">
<!--<input type="hidden" name="confirm" value="0">-->
<input type="hidden" name="changelist" value="{$in.changelist}">
<input type="submit" name="submit" value="ＯＫ"><br>
</form>
<a href="{$g_script}" target=_top>[戻る]</a>
</center>
{include file={$footer_path}}
</body>
</html>