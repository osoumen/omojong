{include file={$header_path}}
<h3>“{$inword}”を追加しました</h3>
<br>
<form action="$page_addword.php" method="post">
さらに追加
<input type="text" name="word" value="">
<input type="submit" name="submit" value="追加"><br>
</form>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}