{include file={$header_path}}
<div id="content_main">
<h3>スタートしました</h3>
<dl>
<dt>人数</dt>
<dd>{$in.ninzuu}〜{$in.ninzuu_max}</dd>
<dt>枚数</dt>
<dd>{$in.maisuu}</dd>
<dt>札の交換</dt>
<dd>{$in.change_quant}回まで{$in.change_amount}枚以内</dd>
</dl>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}