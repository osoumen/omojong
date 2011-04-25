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
<input type="text" name="ninzuu" value="{$in.ninzuu}" size=4>〜<input type="text" name="ninzuu_max" value="{$in.ninzuu_max}" size=4>人
</dd>
<dt>枚数</dt>
<dd>
<input type="text" name="maisuu" value="{$in.maisuu}" size=4>枚
</dd>
<dt>札の交換</dt>
<dd>
<input type="text" name="change_quant" value="{$in.change_quant}" size=4>回まで
<input type="text" name="change_amount" value="{$in.change_amount}" size=4>枚以内
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
