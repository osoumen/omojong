<div class="change_form">
<div class="question">残り{$c_rest}回 {$c_amount}枚以内で交換できます。</div>
{section name=list loop="$stock_array"}
<button onclick="input_changeword({$stock_array[list]},'{$word_array[list]}')" class="change_word">
{$word_array[list]}
</button>
{/section}
<div id="change_input"></div>
<button onclick="reset_change()">クリア</button>
<button onclick="submit_change()">交換</button><br />
<form name="change_form" action="page_change.php" method="post">
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="hidden" name="changelist" value="">
</form>
</div>