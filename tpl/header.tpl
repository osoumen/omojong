<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" CONTENT="text/html; charset=UTF-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
{foreach from="$g_css_url" item="url"}
<link rel="stylesheet" href="{$url}" type="text/css" />
{/foreach}
{foreach from="$g_js_url" item="url"}
<script src="{$url}"></script>
{/foreach}
{if empty($pagetitle)}
<title>{$g_title}</title>
{else}
<title>{$g_title} : {$pagetitle}</title>
{/if}
</head>
<body>
<div id="content">
<div id="header">
<a href="{$g_scripturl}">
<span id="toptitle">{$g_title}</span>
</a>
<div id="usernav">
{if ($g_screen_name !== '')}
Twitter: <span class="its_me">{$g_screen_name}</span> |
<a href="func_logout.php">ログアウト</a>
{else}
<a href="twitter_request.php">Twitterでログイン</a>
{/if}
</div>
</div>
<div id="post_header">
Twitterでいつかつぶやいたかもしれない言葉を使って、素敵なコピーを作って下さい。</div>