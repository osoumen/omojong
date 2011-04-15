{include file={$header_path}}
<hr>
{foreach from="$out_list" item="value" key="key"}
{$key}：{$value}<br>
{/foreach}
<br>を捨てて<br><br>
{foreach from="$in_list" item="value" key="key"}
{$key}：{$value}<br>
{/foreach}
<br>を入手しました。<br><br>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}
