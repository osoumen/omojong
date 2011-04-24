<p>残り{$c_rest}回 {$c_amount}枚以内で交換できます</p>
{section name=list loop="$stock_array"}
<button onclick="input_changeword({$stock_array[list]},'{$word_array[list]}')" class="change_word">
{$word_array[list]}
</button>
<!--
<a href="javascript:input_changeword({$stock_array[list]},'{$word_array[list]}')">
<span class="change_word">{$word_array[list]}</span>
</a>
-->
{/section}
<!--
<a href="javascript:reset_change()">[クリア]</a>
<a href="javascript:submit_change()">[交換]</a><br />
-->
<div id="change_input"></div>
<button onclick="reset_change()">クリア</button>
<button onclick="submit_change()">交換</button><br />
<form name="change_form" action="page_change.php" method="post">
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="hidden" name="changelist" value="">
</form>