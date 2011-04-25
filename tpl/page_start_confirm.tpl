{include file={$header_path}}
<div id="content_main">
<p>以下の条件で始めますか？</p>
<dl>
<dt>人数</dt>
<dd>{$in.ninzuu}〜{$in.ninzuu_max}</dd>
<dt>枚数</dt>
<dd>{$in.maisuu}</dd>
<dt>札の交換</dt>
<dd>{$in.change_quant}回まで{$in.change_amount}枚以内</dd>
</dl>
<form action="page_start.php" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="0">
<input type="hidden" name="ninzuu" value="{$in.ninzuu}">
<input type="hidden" name="ninzuu_max" value="{$in.ninzuu_max}">
<input type="hidden" name="maisuu" value="{$in.maisuu}">
<input type="hidden" name="change_quant" value="{$in.change_quant}">
<input type="hidden" name="change_amount" value="{$in.change_amount}">
<input type="submit" name="submit" value="ＯＫ">
</form>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>[戻る]</a>
</div>
{include file={$footer_path}}