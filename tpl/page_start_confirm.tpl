{include file={$header_path}}
<div id="content_main">
<div class="input_form">
<h3>以下の条件で始めますか？</h3>
<dl>
<dt>参加人数</dt>
<dd>{$in.ninzuu}人〜{$in.ninzuu_max}人</dd>
<dt>使用単語数</dt>
<dd>{$in.maisuu}語</dd>
<dt>単語交換回数</dt>
<dd>{$in.change_quant}回まで{$in.change_amount}語以内</dd>
<dt>公開／非公開</dt>
<dd>
{if isset($in.allow_disclose) && ($in.allow_disclose != 0)}
新着リストに公開する
{else}
新着リストに公開しない
{/if}
</dd>
<dd>
{if isset($in.friends_only) && ($in.friends_only != 0)}
参加をfollowerのみに制限する
{else}
誰でも参加可能
{/if}
</dd>
</dl>
<form action="page_start.php" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="0">
<input type="hidden" name="ninzuu" value="{$in.ninzuu}">
<input type="hidden" name="ninzuu_max" value="{$in.ninzuu_max}">
<input type="hidden" name="maisuu" value="{$in.maisuu}">
<input type="hidden" name="change_quant" value="{$in.change_quant}">
<input type="hidden" name="change_amount" value="{$in.change_amount}">
{if isset($in.allow_disclose)}
<input type="hidden" name="allow_disclose" value="{$in.allow_disclose}">
{else}
<input type="hidden" name="allow_disclose" value="0">
{/if}
{if isset($in.friends_only)}
<input type="hidden" name="friends_only" value="{$in.friends_only}">
{else}
<input type="hidden" name="friends_only" value="0">
{/if}
<input type="submit" class="right_btn" name="submit" value="ＯＫ">
</form>
</div>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}