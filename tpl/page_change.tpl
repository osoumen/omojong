{include file={$header_path}}
<div id="content_main">
<div class="general_container">
{foreach from="$out_list" item="value" key="key"}
『{$value}』<br />
{/foreach}
<p>を捨てて</p>
{foreach from="$in_list" item="value" key="key"}
『{$value}』<br />
{/foreach}
<p>を入手しました。</p>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
</div>
{include file={$footer_path}}
