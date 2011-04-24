{include file={$header_path}}

<b>{$sentence}</b><br /><br />
でよろしいですか？<br />
<form action="page_answer.php" method="post">
<input type="hidden" name="answer" value="{$in.answer}">
<input type="submit" name="submit" value="決定"><br />
</form>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}