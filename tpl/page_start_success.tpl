{include file={$header_path}}
<hr>
<h3>スタートしました</h3>
<table>
<!--<tr><td><b>Username</b></td><td>{$in.username}</td></tr>-->
<tr><td><b>人数</b></td><td>{$in.ninzuu}〜{$in.ninzuu_max}</td></tr>
<tr><td><b>枚数</b></td><td>{$in.maisuu}</td></tr>
<tr><td><b>札の交換</b></td><td>{$in.change_quant}回まで{$in.change_amount}枚以内</td></tr>
</table><br>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}