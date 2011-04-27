<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" CONTENT="text/html; charset=UTF-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" href="{$g_css_url}" type="text/css" />
<script src="js/jquery.js"></script>
{if empty($pagetitle)}
<title>{$g_title}</title>
{else}
<title>{$g_title} / {$pagetitle}</title>
{/if}
</head>
<body>
<div id="content">
<div id="header">
<a href="{$g_scripturl}">
<span id="toptitle">{$g_title}</span>
</a>
{if ($g_screen_name !== '')}
<div id="usernav">
Twitter: <span class="its_me">{$g_screen_name}</span> |
<a href="func_logout.php">ログアウト</a>
</div>
{/if}
</div>
<div id="post_header">
Twitterの発言から抽出した単語を並べ替えて全く新しい文を作成するサイト。
</div>