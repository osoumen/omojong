<?php

require_once 'globals.php';

$in = $_POST;
$c_username = $_COOKIE['username'];

if ($in['username'] == NULL) {$in['username'] = $c_username;}
if ($in['ninzuu'] == NULL) {$in['ninzuu'] = 4;}
if ($in['ninzuu_max'] == NULL) {$in['ninzuu_max'] = 10;}
if ($in['maisuu'] == NULL) {$in['maisuu'] = 12;}
if ($in['change_quant'] == NULL) {$in['change_quant'] = 3;}
if ($in['change_amount'] == NULL) {$in['change_amount'] = 8;}

include 'header.php';

echo <<<EOT
<center><hr>
<h3>新しく始める</h3>
<font color=red>$err_str</font><br>
<form action="$g_script" method="post">
<table>
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="$g_start_confirm">
<tr><td><b>Username</b></td><td><input type="text" name="username" value="$in['username']"></td></tr>
<tr>
<td><b>人数</b></td>
<td><input type="text" name="ninzuu" value="$in['ninzuu']" size=4>〜<input type="text" name="ninzuu_max" value="$in['ninzuu_max']" size=4>人</td>
</tr>
<tr><td><b>枚数</b></td><td><input type="text" name="maisuu" value="$in['maisuu']" size=4>枚</td></tr>
<tr><td><b>札の交換</b></td><td><input type="text" name="change_quant" value="$in['change_quant']" size=4>回まで
<input type="text" name="change_amount" value="$in['change_amount']" size=4>枚以内
</td></tr>
</table>
<input type="submit" name="submit" value="始める"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
EOT;

include 'footer.php';
