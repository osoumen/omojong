{include file={$header_path}}
<div id="content_main">
<p>{$c_username}さんの解答を終了しました。</p>
<p>他のメンバーに知らせますか？</p>
<p>あなたのTwitterアカウントを使用して、自動的に以下のような発言を行います。</p>
<div class="tweet_example">{$twmsg}</div>
<a href="page_ans_end.php" target=_top>[知らせる]</a>
<a href="{$g_script}" target=_top>[知らせない]</a>
</div>
{include file={$footer_path}}