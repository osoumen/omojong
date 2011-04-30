{include file={$header_path}}
<div id="content_main">
<div id="ans_input">{$sentence}</div>
でよろしいですか？<br />
<form action="page_answer.php" method="post">
<input type="hidden" name="answer" value="{$in.answer}" />
<input type="submit" name="submit" value="決定" /><br />
</form>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}