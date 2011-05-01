{include file={$header_path}}
<div id="content_main">
<b>{$sentence}</b><br />
を解答しました。<br />
<p>他のメンバーに知らせますか？</p>
<p>Twitterのmentionを使用して他のメンバーに知らせることが出来ます。</p>
<form class="twitter_form" action="page_msg_send.php" method="post">
<input type="hidden" name="post_token" value="{$post_token}"/>
<input type="hidden" name="post_msg" value=" {$post_msg}"/>
<textarea onKeyup="mojilen(value,0,'{$post_msg}')" class="twitter-field" name="entry_content" tabindex=3 rows="2" cols="50">{$default_msg}</textarea>
<input id="twitter-button0" type="submit" disabled="disabled" value="メッセージをツイート">
</form>
+ {$post_msg}<br />
<span id="msg0"></span>

<a href="{$g_script}" target=_top>戻る</a>
</div>
{include file={$footer_path}}