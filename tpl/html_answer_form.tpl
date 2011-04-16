{section name=list loop="$stock_array"}
<a href="javascript:input_word({$stock_array[list]})">
<span class="ans_word">{$word_array[list]}</span>
</a>
{/section}
<a href="javascript:reset_input()">[リセット]</a>
<a href="javascript:submit_input()">[決定]</a><br>
<form name="answer_form" action="page_answer.php" method="post">
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="hidden" name="answer" value="">
<!--<input type="submit" name="submit" value="これでいい"><br>-->
</form>