<form action="page_addword.php" method="post">
<input type="hidden" name="mode" value="addword">
ロバの耳！みたいな事を吹き込んで下さい。（現在{$totalwords}単語入っています）<br>
<input type="text" name="word" value="">
<input type="submit" name="submit" value="吹き込む"><br>
{if ($todaywords > 0)}
今日は{$todaywords}単語
{/if}
{if ($yesterdaywords > 0)}
昨日は{$yesterdaywords}単語
{/if}
{if ($todaywords > 0) || ($yesterdaywords > 0)}
追加されました。<br>
{/if}
</form>