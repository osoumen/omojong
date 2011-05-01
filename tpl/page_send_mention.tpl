{include file={$header_path}}
<div id="content_main">
<div class="general_container">
<h3>{$message}</h3>
<p>他のメンバーに知らせますか？</p>
<p>Twitterのmentionを使用して他のメンバーに知らせることが出来ます。</p>
<p>あなたのアカウントから以下の宛先ににmentionを送ります。</p>
{foreach from="$to" item="value"}
@{$value}<br />
{/foreach}
<div class="twitter_input">
<form class="twitter_form" action="page_msg_send.php" method="post">
<input type="hidden" name="post_token" value="{$post_token}"/>
<input type="hidden" name="post_msg" value=" {$post_msg}"/>
<textarea onKeyup="mojilen(value,0,'{$post_msg}')" class="twitter-field" name="entry_content" tabindex=3 rows="2" cols="45">{$default_msg}</textarea>
<span id="msg0"></span>
<input id="twitter-button0" type="submit" value="メッセージをツイート">
</form>
<div class="post_msg">+ {$post_msg}</div>
</div>
</div>
<div id="pre_footer">
<a href="{$g_script}" target=_top>戻る</a>
</div>
</div>
{include file={$footer_path}}
