{include file={$header_path}}
<div id="content_main">
{foreach from="$disp_list" item="value" key="key"}
『{$value}』<br />
{/foreach}
<p>を捨ててもよろしいですか？</p>
<form action="page_change.php" method="post">
<!--<input type="hidden" name="confirm" value="0">-->
<input type="hidden" name="changelist" value="{$in.changelist}">
<input type="submit" name="submit" value="ＯＫ"><br />
</form>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>[戻る]</a>
</div>
{include file={$footer_path}}