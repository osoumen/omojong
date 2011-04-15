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
{foreach from="$out_list" item="value" key="key"}
{$key}：{$value}<br>
{/foreach}
<br>を捨てて<br><br>
{foreach from="$in_list" item="value" key="key"}
{$key}：{$value}<br>
{/foreach}
<br>を入手しました。<br><br>
<a href="{$g_script}" target=_top>[戻る]</a>
</center>
{include file={$footer_path}}
</body>
</html>
