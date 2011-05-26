{include file={$header_path}}
<div id="content_main">
{if $err_str != ''}
<div class="err_msg">{$err_str}</div>
{/if}
<div class="input_form">
<h3>ルールを設定してください。</h3>
<form action="page_start.php" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="1">
<dl>
<dt>共同メンバー</dt>
<dd>
あなたの発言に加えて、あなたのフォロワーの発言から、単語を集めることができます。
メンバー全員の最新の100発言から、ランダムに単語を拾います。途中参加した人からは単語を取得しません。
開始すると、自動的にあなたから各メンバーにDMでお知らせをします。</dd>
<dd>
<span id="member_input_area"></span>
<div id="progress"><img src="images/progress2.gif" width=24 height="24" /></div>
</dd>
<!--
<dt>使用単語数</dt>
<dd>参加者にランダムに配られる単語カードの数です。カードを使い切るまで、何回でも解答することができます。</dd>
<dd>
<select name="maisuu">
{html_options options=$maisuu_options selected=$in.maisuu}
</select>
</dd>
-->
<dt>単語交換回数</dt>
<dd>単語カードの交換回数を設定します。
交換回数が残っていても、最大語数を交換すると交換できなくなります。
語数が残っていても、回数を使い切ると交換できなくなります。</dd>
<dd>
<select name="change_quant">
{html_options options=$change_quant_options selected=$in.change_quant}
</select>
<select name="change_amount">
{html_options options=$change_amount_options selected=$in.change_amount}
</select>
</dd>
<dt>解答期限</dt>
<dd>期限が過ぎると自動的に終了し、結果が公開されます。1週間以内の日付と時刻を指定して下さい。<dd>
<dd>
<select name="end_date">
{html_options options=$end_date_options selected=$in.end_date}
</select>
<select name="end_hour">
{html_options options=$end_hour_options selected=$in.end_hour}
</select>
</dd>
<dt>公開／非公開</dt>
<dd>あなたのフォロワーだけしか途中参加できないようにします。設定すると、開始した事を新着リストに公開しません。</dd>
<dd>
<input type='checkbox' name='friends_only' value='1' 
{if isset($in.friends_only)}
{if ($in.friends_only != 0)}
checked='checked'
{/if}
{/if}
>
非公開にする
</dd>
</dl>
<input class="right_btn" type="submit" name="submit" value="この条件で始める">
</form>
</div>
</div>
<script type="text/javascript">
$(function(){
$('#progress')
.ajaxStart(function() {
	$(this).show();
})
.ajaxStop(function() {
	$(this).hide();
});
$.getJSON("data_follows.php", function(json) {
	$("#member_input_area").html('<input type="text" name="members" id="member_input" />');
	$('#member_input').alphanumeric({ allow:"_," });
	$('#member_input').autoSuggest(json, {
	asHtmlID: 'members',
	neverSubmit: true,
	minChars: 1,
	matchCase: false,
	startText: 'フォロワーIDを入力',
	emptyText: '見つかりませんでした',
	keyDelay: 20,
	formatList: function(data, elem){
		var my_image = data.image;
		var new_elem = elem.html(
		'<img class="tw_img" src="' + data.image + '" />' +
		data.name + ' (' + data.value + ')<span class="clearfix"></span>'
		);
		return new_elem;
		}
	});		
});
});
</script>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}
