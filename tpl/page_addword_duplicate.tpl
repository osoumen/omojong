{include file={$header_path}}
<div id="content_main">
<h3>“{$inword}”は既にありますが追加しますか？</h3>
<form action="$page_addword.php" method="post">
<input type="hidden" name="word" value="{$inword}">
<input type="hidden" name="forceadd" value="1">
<input type="submit" name="submit" value="追加する"><br />
</form>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>[やめる]</a>
</div>
{include file={$footer_path}}