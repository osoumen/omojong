{include file={$header_path}}
<div id="content_main">
{if $err_str != ''}
<p><span class="err_msg">{$err_str}</span></p>
{/if}
<div class="input_form">
<h3>ルールを設定してください。</h3>
<form action="page_start.php" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="{$g_start_confirm}">
<dl>
<dt>参加メンバー</dt>
<dd>参加して欲しい人をあなたをフォローしている人から選んで下さい。</dd>
<dd><input type="text" name="members" value="{$in.members}"></dd>
<dt>参加人数</dt>
<dd>必要人数と最大人数を設定します。必要人数が集まると解答出来るようになります。
集まった時点での参加者の発言を取得します。それ以降の途中参加者の単語は取得しません。</dd>
<dd>
<select name="ninzuu">
{html_options options=$ninzuu_options selected=$in.ninzuu}
</select>
〜
<select name="ninzuu_max">
{html_options options=$ninzuu_options selected=$in.ninzuu_max}
</select>
</dd>
<dt>使用単語数</dt>
<dd>参加者にランダムに配られる単語カードの数です。カードを使い切るまで、何回でも解答することができます。</dd>
<dd>
<select name="maisuu">
{html_options options=$maisuu_options selected=$in.maisuu}
</select>
</dd>
<dt>単語交換回数</dt>
<dd>配られたカードの交換回数を設定できます。
交換回数が残っていても、最大語数を交換すると交換できなくなります。
また、語数が残っていても、回数を使い切ると交換できなくなります。</dd>
<dd>
<select name="change_quant">
{html_options options=$change_quant_options selected=$in.change_quant}
</select>
<select name="change_amount">
{html_options options=$change_amount_options selected=$in.change_amount}
</select>
</dd>
<dt>解答期限</dt>
<dd>1週間以内の日付と時刻を指定して下さい。期限が過ぎると自動的に終了し、そこまでの結果が公開されます。<dd>
<dd>
<select name="end_date">
{html_options options=$end_date_options selected=$in.end_date}
</select>
<select name="end_hour">
{html_options options=$end_hour_options selected=$in.end_hour}
</select>
</dd>
<dt>公開／非公開</dt>
<dd>「新着リストに公開する」に設定すると、あなたが開始した事を全体に公開します。</dd>
<dd>
<input type='checkbox' name='allow_disclose' value='1' 
{if isset($in.allow_disclose)}
{if ($in.allow_disclose != 0)}
checked='checked'
{/if}
{/if}
>
新着リストに公開する
</dd>
<dd>あなたをフォローしている人だけ参加できるようにします。</dd>
<dd>
<input type='checkbox' name='friends_only' value='1' 
{if isset($in.friends_only)}
{if ($in.friends_only != 0)}
checked='checked'
{/if}
{/if}
>
参加をfollowerのみに制限する
</dd>
</dl>
<input class="right_btn" type="submit" name="submit" value="この条件で始める">
</form>
</div>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}
