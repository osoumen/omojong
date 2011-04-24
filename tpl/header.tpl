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
<a href="func_logout.php">[ログアウト]</a>
<h1>{$g_title}</h1>
