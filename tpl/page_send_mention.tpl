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
	post_token: '{$post_token}',
	post_msg: '{$post_msg}',
	entry_content: '{$default_msg}'
	};
	$.post("page_msg_send.php", post_msg, function(text, status) {
		if ( text == 'ok' ) {
			$('#progress_ok').show("fast");
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
<div class="general_container">
<h3>{$message}</h3>
<div id="err_msg"></div>
<p>他のメンバーに知らせますか？</p>
<p>Twitterのmentionを使用して他のメンバーに知らせることが出来ます。</p>
<p>あなたのアカウントから以下の宛先ににmentionを送ります。</p>
{foreach from="$to" item="value"}
<p>@{$value}</p>
{/foreach}
<div id="twitter_form" class="general_container">
{$default_msg} {$post_msg}
</div>
<button id="twitter-button0">メッセージをツイート</button>
<div id="progress"><p>ツイート処理中...</p><img src="images/progress.gif" width=64 height="32" /></div>
<div id="progress_ok">ツイートしました。</div>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
</div>
{include file={$footer_path}}
