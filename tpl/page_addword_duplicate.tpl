{include file={$header_path}}
<h3>“{$inword}”は既にありますが追加しますか？</h3>
<form action="$page_addword.php" method="post">
<input type="hidden" name="word" value="{$inword}">
<input type="hidden" name="forceadd" value="1">
<input type="submit" name="submit" value="追加する"><br />
</form>
<a href="{$g_script}" target=_top>[やめる]</a>
{include file={$footer_path}}