<div class="pastlog_content">
<div class="creater_name">
{$kaitousya}
</div>
<div class="ans_present">{$sentence}</div>
<span class="date_present">{$date}</span>
・
<span class="good_num">{$hyousuu}</span>
・
<a class="like_btn" href="page_vote.php?{$pastlog_param_name}={$pastno}&ansnum={$ansindex}">
おみごと！
</a>
<div class="twitter_input">
{if ($g_screen_name !== '')}
<form class="twitter_form" action="func_tweet.php" method="post">
<input type="hidden" name="post_token" value="{$post_token}"/>
<input type="hidden" name="tweet_msg" value="{$tweet_msg}"/>
<textarea onKeyup="mojirest(value,{$ansindex},'{$tweet_msg}')" class="twitter-field" name="entry_content" tabindex=3 rows="2" cols="50"></textarea>
<span id="msg{$ansindex}">残り</span>
<input id="twitter-button{$ansindex}" type="submit" disabled="disabled" value="コメントをツイート">
</form>
<div class="post_msg">+ {$tweet_msg}</div>
{else}
<p>Twitterでログインするとコメントが出来ます。</p>
{/if}
</div>
<div class="timeline">
	<script>
	new TWTR.Widget({
	  version: 2,
	  type: 'search',
	  search: '{$wj_search}',
	  interval: 6000,
	  title: 'Twitterの反応',
	  subject: 'コメント',
	  width: 'auto',
	  height: '100',
	  theme: {
		shell: {
		  background: '#ffffff',
		  color: '#444444'
		},
		tweets: {
		  background: '#ffffff',
		  color: '#444444',
		  links: '#FF6699'
		}
	  },
	  features: {
		scrollbar: true,
		loop: false,
		live: true,
		hashtags: true,
		timestamp: true,
		avatars: true,
		behavior: 'all'
	  }
	}).render().start();
	</script>
</div>
</div>