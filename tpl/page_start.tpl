{include file={$header_path}}
<div id="content_main">
{if $err_str != ''}
<p><span class="err_msg">{$err_str}</span></p>
{/if}
<div class="input_form">
<form action="page_start.php" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="{$g_start_confirm}">
<dl>
<dt>人数</dt>
<dd>
<select name="ninzuu">
{html_options options=$ninzuu_options selected=$in.ninzuu}
</select>
〜
<select name="ninzuu_max">
{html_options options=$ninzuu_options selected=$in.ninzuu_max}
</select>
</dd>
<dt>枚数</dt>
<dd>
<select name="maisuu">
{html_options options=$maisuu_options selected=$in.maisuu}
</select>
</dd>
<dt>交換</dt>
<dd>
<select name="change_quant">
{html_options options=$change_quant_options selected=$in.change_quant}
</select>
<select name="change_amount">
{html_options options=$change_amount_options selected=$in.change_amount}
</select>
</dd>
</dl>
<input class="right_btn" type="submit" name="submit" value="この条件で始める">
</form>
</div>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>[戻る]</a>
</div>
{include file={$footer_path}}
