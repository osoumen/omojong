<table>
<tr><th>番号</th><th>持ち札</th></tr>
{section name=list loop="$stock_array"}
<tr><td align=right>{$stock_array[list]}</td><td>{$word_array[list]}</td></tr>
{/section}
</table><br>