<div class="answer_form">
<div class="question">以下の単語を使って、文を作って下さい。</div>
{section name=list loop="$stock_array"}
<button onclick="input_word({$stock_array[list]},'{$word_array[list]}')" class="ans_word">
{$word_array[list]}
</button>
{/section}
<div id="ans_input"></div>
<button onclick="reset_input()">クリア</button>
<button onclick="submit_input()">決定</button>
<form name="answer_form" action="page_answer.php" method="post">
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="hidden" name="answer" value="">
</form>
</div>