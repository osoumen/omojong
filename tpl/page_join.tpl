<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<title>Twitterおもじゃん:参加</title>
</head>
<body>
<center>
{include file={$header_path}}
<hr>
<h3>参加しました。</h3>
Username：{$in.username}<br>
あなたのツイートから単語を抽出することができます。<br>
(最近の100発言を使用します。重複した単語は１つのみ登録されます。取得された単語は単語札以外には使用されません。)<br>
<a href="page_word_form_tw.php">[抽出する]</a>
<br>
<a href="{$g_script}" target=_top>[抽出しない]</a>
</center>
{include file={$footer_path}}
</body>
</html>
