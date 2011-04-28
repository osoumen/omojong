<div class="pastlog_content">
<div class="creater_name"><a href="http://twitter.com/{$kaitousya}" target="_blank">{$kaitousya}</a></div>
<a name="{$ansindex}" href="http://twitter.com/home?status={$tweet_msg}" target="_blank"><div class="ans_present">{$sentence}</div></a>
<span class="date_present">{$date}</span>
・
<span class="good_num">{$hyousuu}</span>
・
<a class="like_btn" href="page_vote.php?{$pastlog_param_name}={$pastno}&ansnum={$ansindex}">
いいね！
</a>
<div class="timeline">
	<script src="http://widgets.twimg.com/j/2/widget.js"></script>
	<script>
	new TWTR.Widget({
	  version: 2,
	  type: 'search',
	  search: '{$wj_search}',
	  interval: 6000,
	  title: 'Twitter Widget',
	  subject: 'Twitterの反応',
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
		  links: '#33ccff'
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