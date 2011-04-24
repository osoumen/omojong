{include file={$header_path}}

{foreach from="$disp_list" item="value" key="key"}
『{$value}』<br />
{/foreach}
<p>を捨ててもよろしいですか？</p>
<form action="page_change.php" method="post">
<!--<input type="hidden" name="confirm" value="0">-->
<input type="hidden" name="changelist" value="{$in.changelist}">
<input type="submit" name="submit" value="ＯＫ"><br />
</form>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}