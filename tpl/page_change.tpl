{include file={$header_path}}

{foreach from="$out_list" item="value" key="key"}
『{$value}』<br>
{/foreach}
<p>を捨てて</p>
{foreach from="$in_list" item="value" key="key"}
『{$value}』<br>
{/foreach}
<p>を入手しました。</p>
<a href="{$g_script}" target=_top>[戻る]</a>
{include file={$footer_path}}
