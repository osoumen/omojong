<form action="page_change.php" method="post">
交換したい札の番号をコンマで区切りで入力してください。<br>
<input type="hidden" name="confirm" value="{$g_answer_confirm}">
<input type="text" name="changelist" value="">
<input type="submit" name="submit" value="交換"><br>
残り{$c_rest}回 {$c_amount}枚以内で交換できます<br>
</form>