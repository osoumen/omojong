<div class="answer_form">
<div class="question">以下の単語を使用して、文を作って下さい。</div>
{section name=list loop="$stock_array"}
<button onclick="input_word({$stock_array[list]},'{$word_array[list]}')" class="ans_word">
{$word_array[list]}
</button>
<!--
<a href="javascript:input_word({$stock_array[list]},'{$word_array[list]}')">
<span class="ans_word">{$word_array[list]}</span>
</a>
-->
{/section}
<div id="ans_input"></div>
<button onclick="reset_input()">クリア</button>
<button onclick="submit_input()">決定</button>
<!--
<a href="javascript:reset_input()">[クリア]</a>
<a href="javascript:submit_input()">[解答]</a><br />
-->
<form name="answer_form" action="page_answer.php" method="post">
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="hidden" name="answer" value="">
</form>
</div>