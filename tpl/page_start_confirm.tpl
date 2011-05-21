{include file={$header_path}}
<script type="text/javascript">
$(function() {
$('#progress')
.ajaxStart(function() {
	$(this).show();
})
.ajaxStop(function() {
	$(this).hide();
});
$('#twitter-button0').click(function() {
	$('#twitter-button0').hide("fast");
	var post_msg = {
	members: '{$in.members}',
	confirm: 0,
	ninzuu: '{$in.ninzuu}',
	ninzuu_max: '{$in.ninzuu_max}',
	maisuu: '{$in.maisuu}',
	change_quant: '{$in.change_quant}',
	change_amount: '{$in.change_amount}',
	end_date: '{$in.end_date}',
	end_hour: '{$in.end_hour}',
{if isset($in.friends_only)}
	friends_only: 1,
{else}
	friends_only: 0,
{/if}
	entry_content: '{$default_msg}'
	};
	$.post("page_start.php", post_msg, function(text, status) {
		if ( text == 'ok' ) {
			document.location = "{$g_script}";
		}
		else {
			$('#err_msg').html(text);
			$('#twitter-button0').show("fast");
		}
	});
});
});
</script>
<div id="content_main">
<div class="input_form">
<h3>以下の条件で始めますか？</h3>
<div id="err_msg"></div>
<dl>
{if ($init_members)}
<dt>参加メンバー</dt>
{/if}
{foreach from="$init_members" item="value"}
<dd>@{$value}</dd>
{/foreach}
<!--
<dt>参加人数</dt>
<dd>{$in.ninzuu}人〜{$in.ninzuu_max}人</dd>
<dt>使用単語数</dt>
<dd>{$in.maisuu}語</dd>
-->
<dt>単語交換回数</dt>
<dd>{$in.change_quant}回まで{$in.change_amount}語以内</dd>
<dt>解答期限</dt>
<dd>{$datetext}</dd>
<dt>公開／非公開</dt>
<dd>
{if isset($in.friends_only) && ($in.friends_only != 0)}
非公開にする
{else}
誰でも途中参加が可能
{/if}
</dd>
{if ($init_members)}
<dt>ダイレクトメッセージの送信</dt>
<dd>このDMでメンバーに知らせます。</dd>
<dd><div class="general_container">{$default_msg} {$post_msg}</div></dd>
{/if}
</dl>
<button id="twitter-button0" class="right_btn">ＯＫ</button>
<div id="progress"><p>ツイートを分析中...</p><img src="img/progress.gif" width=64 height="32" /></div>
</div>
</div>
<div id="pre_footer">
<a href="page_start.php" target=_top>戻る</a>
</div>
{include file={$footer_path}}