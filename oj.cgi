#!/usr/local/bin/perl

use Encode;
use Net::Twitter::Lite;
#use utf8;
use CGI::Lite;
use DBI;

#GKDB面雀 2.0
$version = '2.0';

#  [ 設置例 ]
#
#  cgi-bin [755]
#       |
#       +-- ojcgi / [707]（必須）
#             |        oj.cgi       [705]（必須）
#             |        words.dat    [600]
#             |        kaitou.dat   [600]
#             |
#             +-- past / [700]（必須）

#「今思い浮かんだ言葉」フォーム設置方法

#<form action="http://サイトのURL/cgi-bin/ojcgi/oj.cgi" method="post">
#<input type="hidden" name="mode" value="addword">
#今思い浮かんだ言葉：<input type="text" name="word" value="">
#<input type="submit" name="submit" value="送信"><br>
#</form>


#基本設定--ここから

$g_title = '面雀(おもじゃん)2.0';		# タイトル
$g_backurl = '../../index.html';		# 戻りのURL
$g_script = './oj.cgi';				#このファイル自身（705）

$g_scripturl = 'http://benjamin-lab.com/~ojbot/oj.cgi';

$g_kekkasort = 0;			#結果発表を投票数順に表示する（0なら投稿順）

$g_start_confirm = 1;		#開始の確認画面を表示する？(1=YES 0=NO)
$g_answer_confirm = 1;	#投稿の確認画面を表示する？(1=YES 0=NO)
$g_giveup_confirm = 1;	#投了の確認画面を表示する？(1=YES 0=NO)

$g_maxwords = 0;			#保持する単語の最大数　超えると古いものから消えていく　０だと無制限

#スタイルシートの設定
$g_css_url = 'css/default.css';

my $g_database = 'omojong';
my $g_dbuser = 'ojbot';
my $g_dbpassword = 'korogottu';

$g_kaitoufile = 'kaitou.dat';		#解答を記録するファイル（600）
$g_pastlogdir = './past';			#過去ログを補完するディレクトリ（700）

#mentionによる通知を使用する(1=YES 0=NO)
$usenotification = 0;

#Twitter関連
my %g_consumer_tokens = (
    consumer_key    => 'oVHQOYjXkfrEOGEVdRosQ',
    consumer_secret => '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8',
);
my $access_token        = '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT';
my $access_token_secret = '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg';

#参加したときの通知の内容
$notifymsg0=<<"_EOF_";
ご参加ありがとうございます！まだまだ参加受付中です。 ($g_title $g_scripturl)
_EOF_

#参加人数が集まったときの通知の内容
$notifymsg1=<<"_EOF_";
参加人数が集まりました。解答受付中です！ ($g_title $g_scripturl)
_EOF_

#解答が終わったときの通知の内容
$notifymsg2=<<"_EOF_";
解答が出揃いました。結果を見られます！ ($g_title $g_scripturl)
_EOF_


$crypt_key = 'test';

#--設定はここまで



#--メイン処理はここから

my $cgi = new CGI::Lite;
$cgi->set_platform(Unix);

&load_session_table;
&modecheck;

#$lfh = my_flock() or &error("他の人がアクセス中です。再度アクセスしてみてください。");

if ($mode eq "start") {&mode_start;}
elsif ($mode eq "join") {&mode_join;}
elsif ($mode eq "repaircookie") {&mode_repaircookie;}
elsif ($mode eq "joincancel") {&mode_joincancel;}
elsif ($mode eq "change") {&mode_change;}
elsif ($mode eq "answer") {&mode_answer;}
elsif ($mode eq "giveup") {&mode_giveup;}
elsif ($mode eq "vote") {&mode_vote;}
elsif ($mode eq "addword") {&mode_addword;}
elsif ($mode eq "get") {&mode_get;}
elsif ($mode eq "pastlog") {&mode_pastlog;}
elsif ($mode eq "help") {&mode_help;}
elsif ($phase eq "sanka") {&phase_sanka;}
elsif ($phase eq "toukou") {&phase_toukou;}
elsif ($phase eq "kekka") {&phase_kekka;}
else {&phase_kekka;}

#my_funlock($lfh);

exit;


#  htmlヘッダー
sub html_header {
	$head_flag=1;
	print "Content-type: text/html\n\n";
	print<<"_EOF_";
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<link rel="stylesheet" href="$g_css_url" type="text/css" />
<title>$g_title</title>
</head>
<body>
<center>
<font color=#000000 size=7>$g_title</font><br>
_EOF_
}

# フッター部分
sub html_footer {
#著作権表示部分
    print<<"_EOF_";
</center>
<hr>
<div align="center"><tt>
面雀$version <a href="http://www.benjamin-lab.com/" target=_blank>benjamin-lab.com</a>
</tt></div>
</body></html>
_EOF_
}

sub modecheck {
	%in = $cgi->parse_form_data;
	if (exists($in{'mode'})) {
		$mode=$in{'mode'};
	}
	$phase = $session{'phase'};
	if ($phase eq "") {$phase = 'kekka';}
}


#結果発表状態の処理
sub phase_kekka {
	&load_words_table;
	
	&html_header;
	print<<"_EOF_";
<hr>
<a href="$g_backurl" TARGET=_top>[ホームへ]</a>
<a href="$g_script?mode=help" TARGET=_top>[使い方]</a>
<a href="$g_script?mode=pastlog">[過去の記録]</a>
<hr>
_EOF_

	print_kekka(-1);
	
	print<<"_EOF_";
<hr>
<a href="$g_script?mode=start&new=1">[新しく始める]</a><br>
<br>
_EOF_
	&print_addwordform;
	&html_footer;
}

#参加受付状態での処理
sub phase_sanka {
	my($rest,$nametext);
	&get_cookie;
	&load_words_table;
	
	&html_header;
	print<<"_EOF_";
<hr>
<a href="$g_backurl" TARGET=_top>[ホームへ]</a>
<a href="$g_script?mode=help" TARGET=_top>[使い方]</a>
<a href="$g_script?mode=pastlog">[過去の記録]</a>
<hr>
<h2>求む！参加者</h2>
_EOF_
	
	print "<table border=0>\n";
	print "<tr><th>参加者</th></tr>\n";
	foreach (@members) {
		if (($c_passwd eq $passwd{$c_username}) and ($_ eq $c_username)) {
			$nametext = "<font size=+1><b>$_</b></font>";
		}
		else {
			$nametext = $_;
		}
		print "<tr><td>$nametext さん</td></tr>";
	}
	print "</table><br>\n";
	
	$rest = $session{'ninzuu'} - @members;
	print "あと$rest人の参加が必要です。<br><br>\n";
	
	if ($c_passwd ne $passwd{$c_username}) {
		#参加者以外の場合
		#参加表明フォームを表示
		print<<"_EOF_";
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="join">
<table>
<tr><td><b>Username</b></td><td><input type="text" name="username" value=""></td></tr>
<tr><td><b>cookie再発行用パスワード</b></td><td><input type="password" name="passwd" value=""></td></tr>
</table>
<input type="submit" name="submit" value="参加"><br>
</form>
<hr>
<a href="$g_script?mode=repaircookie">cookieの再発行（参加者）</a>
_EOF_
	}
	else {
		#参加者の場合
		#参加取り消しボタンを表示
		if ($c_username ne $members[0]) {
			print<<"_EOF_";
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="joincancel">
<input type="submit" name="submit" value="参加を取り消す"><br>
</form>
_EOF_
		}
	}
	print "<br>";
	&print_addwordform;
	&html_footer;
}

#解答受付状態での処理
sub phase_toukou {
	my($ans,$kaitousya,@anslist,$sentence,@kaitoufiledata,$nametext,$word);

	&get_cookie;
	&load_words_table;
	
	&html_header;
	print<<"_EOF_";
<hr>
<a href="$g_backurl" TARGET=_top>[ホームへ]</a>
<a href="$g_script?mode=help" TARGET=_top>[使い方]</a>
<a href="$g_script?mode=pastlog">[過去の記録]</a>
<hr>
<h3>参加者熟考中</h3>
_EOF_

	print "<table border=0>\n";
	print "<tr><th>参加者</th><th>解答状況</th></tr>\n";
	foreach (@members) {
		$nametext = $_;
		if (($c_passwd eq $passwd{$c_username}) and ($_ eq $c_username)) {
			$nametext = "<font size=+1><b>$_</b></font>";
		}
		else {
			$nametext = $_;
		}
		if ($stock{$_} eq "") {
			print "<tr><td>$nametext さん</td><td><font color=blue>解答終了</font></td></tr>\n";
		}
		else {
			print "<tr><td>$nametext さん</td><td><font color=red>解答中</font></td></tr>\n";
		}
	}
	print "</table><br>\n";

	if ($c_passwd ne $passwd{$c_username}) {
		#参加者以外
		if (@members < $session{'ninzuu_max'}) {
			print<<"_EOF_";
<form action="$g_script" method="post">
<b>途中参加受付中！</b><br>
<input type="hidden" name="mode" value="join">
<table>
<tr><td><b>Username</b></td><td><input type="text" name="username" value=""></td></tr>
<tr><td><b>cookie再発行用パスワード</b></td><td><input type="password" name="passwd" value=""></td></tr>
</table>
<input type="submit" name="submit" value="参加"><br>
</form>
_EOF_
		}
		print "<hr><a href=\"$g_script?mode=repaircookie\">cookieの再発行（参加者）</a><br>";
	}
	else {
		#参加者である場合
		#既に投稿した解答を表示
		if ($#kaitoufiledata < 0) {
			open IN, "$g_kaitoufile";
			@kaitoufiledata = <IN>;
			close IN;
		}
		print "<table border=0>\n";
		print "<tr><th>$c_username さんが投稿した解答</th></tr>\n";
		foreach (@kaitoufiledata) {
			chomp;
			(undef,$ans,$kaitousya) = split(/<>/);
			@anslist = split(/,/,$ans);
			$sentence = numlist2sentence(@anslist);
			if ($kaitousya eq $c_username) {print "<tr><th>$sentence</th></tr>\n";}
		}
		print "</table><br>\n";
		
		#持ち札の一覧を表示
		if ($stock{$c_username}) {
			&load_words_table;
			print "<table border=0>\n";
			print "<tr><th>番号</th><th>持ち札</th></tr>\n";
			foreach (split(/,/,$stock{$c_username})) {
				$word = $words[$_];
				print "<tr><td align=right>$_</td><td>$word</td></tr>\n";
			}
			print "</table><br>\n";
			
			#投稿フォームを表示
			print<<"_EOF_";
<form action="$g_script" method="post">
札の番号をコンマで区切りで入力して文を作ってください。<br>
<input type="hidden" name="mode" value="answer">
<input type="hidden" name="confirm" value="$g_answer_confirm">
<input type="text" name="answer" value="">
<input type="submit" name="submit" value="解答"><br>
</form>
_EOF_
			#交換回数が残っている
			if ($changerest{$c_username} > 0) {
			print<<"_EOF_";
<form action="$g_script" method="post">
交換したい札の番号をコンマで区切りで入力してください。<br>
<input type="hidden" name="mode" value="change">
<input type="hidden" name="confirm" value="$g_answer_confirm">
<input type="text" name="changelist" value="">
<input type="submit" name="submit" value="交換"><br>
残り$changerest{$c_username}回 $change_amount{$c_username}枚以内で交換できます<br>
</form>
_EOF_
			}
			#投了ボタンを表示
			print<<"_EOF_";
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="giveup">
<input type="hidden" name="confirm" value="$g_giveup_confirm">
<input type="submit" name="submit" value="投了する"><br>
</form>
_EOF_
		}
		else {
			print "$c_usernameさんはもう解答できません。<br>\n";
		}
	}
	
	print "<br>";
	&print_addwordform;
	&html_footer;
}


sub mode_start {
	my($err_str,@no_yes);
	@no_yes = ('なし','あり');
	
	&get_cookie;
	&load_words_table;
	
	if (!$in{'new'}) {
		if ($phase ne 'kekka') { $err_str = '開催中です。'; }
		elsif ($in{'username'} eq '') { $err_str = '名前を入力してください。'; }
		elsif ($in{'ninzuu'} eq '') { $err_str = '人数のパラメータがありません。'; }
		elsif (grep(/\D/,$in{'ninzuu'})) {$err_str = '人数には数値を指定してください。';}
		elsif ($in{'ninzuu'} < 2) { $err_str = '２人以上の人数が必要です。'; }
		elsif (grep(/\D/,$in{'ninzuu_max'})) {$err_str = '人数には数値を指定してください。';}
		elsif ($in{'ninzuu_max'} < $in{'ninzuu'}) { $err_str = '最大人数が最少人数より少ないです。'; }
		elsif (grep(/\D/,$in{'maisuu'})) {$err_str = '枚数には数値を指定してください。';}
		elsif ($in{'maisuu'} < 4) { $err_str = '枚数が少なすぎます。'; }
		elsif (grep(/\D/,$in{'change_quant'})) {$err_str = '交換可能回数は数値で指定してください。';}
		elsif ($in{'change_quant'} < 0) { $err_str = '交換可能回数の数値が不正です。'; }
		elsif (grep(/\D/,$in{'change_amount'})) {$err_str = '交換可能枚数は数値で指定してください。';}
		elsif ($in{'change_amount'} < 0) { $err_str = '交換可能枚数の数値が不正です。'; }
		elsif ($in{'passwd'} eq "") { $err_str = 'パスワードを設定して下さい。'; }
		elsif ($totalwords < $in{'ninzuu'}*$in{'maisuu'}) {
			$err_str = '札が足りません。';
		}
		if ($in{'ninzuu_max'} eq '') {
			$in{'ninzuu_max'} = $in{'ninzuu'};
		}
		if ($in{'maisuu'} eq '') {
			$in{'maisuu'} = 10;
		}
	}
	if ($in{'new'} or $err_str) {
		if ($in{'username'} eq "") {$in{'username'} = $c_username;}
		if ($in{'ninzuu'} eq "") {$in{'ninzuu'} = 4;}
		if ($in{'ninzuu_max'} eq "") {$in{'ninzuu_max'} = 10;}
		if ($in{'maisuu'} eq "") {$in{'maisuu'} = 12;}
		if ($in{'change_quant'} eq "") {$in{'change_quant'} = 3;}
		if ($in{'change_amount'} eq "") {$in{'change_amount'} = 8;}
		
		&html_header;
		print<<"_EOF_";
<center><hr>
<h3>新しく始める</h3>
<font color=red>$err_str</font><br>
<form action="$g_script" method="post">
<table>
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="$g_start_confirm">
<tr><td><b>Username</b></td><td><input type="text" name="username" value="$in{'username'}"></td></tr>
<tr><td><b>パスワード(cookie再発行用)</b></td><td><input type="password" name="passwd" value="$in{'passwd'}"></td></tr>
<tr>
<td><b>人数</b></td>
<td><input type="text" name="ninzuu" value="$in{'ninzuu'}" size=4>〜<input type="text" name="ninzuu_max" value="$in{'ninzuu_max'}" size=4>人</td>
</tr>
<tr><td><b>枚数</b></td><td><input type="text" name="maisuu" value="$in{'maisuu'}" size=4>枚</td></tr>
<tr><td><b>札の交換</b></td><td><input type="text" name="change_quant" value="$in{'change_quant'}" size=4>回まで
<input type="text" name="change_amount" value="$in{'change_amount'}" size=4>枚以内
</td></tr>
</table>
<input type="submit" name="submit" value="始める"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		&html_footer;
	}
	else {
		if ($in{'confirm'}) {
			&html_header;
			print<<"_EOF_";
<hr>
<h3>新しく開始</h3>
以下の条件で始めますか？<br>
<table>
<tr><td><b>Username</b></td><td>$in{'username'}</td></tr>
<tr><td><b>パスワード</b></td><td>$in{'passwd'}</td></tr>
<tr><td><b>人数</b></td><td>$in{'ninzuu'}〜$in{'ninzuu_max'}</td></tr>
<tr><td><b>枚数</b></td><td>$in{'maisuu'}</td></tr>
<tr><td><b>札の交換</b></td><td>$in{'change_quant'}回まで$in{'change_amount'}枚以内
</td></tr>
</table>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="start">
<input type="hidden" name="confirm" value="0">
<input type="hidden" name="username" value="$in{'username'}">
<input type="hidden" name="passwd" value="$in{'passwd'}">
<input type="hidden" name="ninzuu" value="$in{'ninzuu'}">
<input type="hidden" name="ninzuu_max" value="$in{'ninzuu_max'}">
<input type="hidden" name="maisuu" value="$in{'maisuu'}">
<input type="hidden" name="change_quant" value="$in{'change_quant'}">
<input type="hidden" name="change_amount" value="$in{'change_amount'}">
<input type="submit" name="submit" value="ＯＫ">
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
			&html_footer;
		}
		else {
			#解答ファイルの内容を過去ログに移す
			&refresh_kaitou_table;

			undef(%session);
			undef(@members);
			undef(%passwd);
			undef(%stock);
			undef(%changerest);
			
			$session{'ninzuu'} = $in{'ninzuu'};
			$session{'ninzuu_max'} = $in{'ninzuu_max'};
			$session{'maisuu'} = $in{'maisuu'};
			$session{'change_quant'} = $in{'change_quant'};
			$session{'change_amount'} = $in{'change_amount'};
	
			$members[0] = $in{'username'};
			$passwd{$in{'username'}} = crypt($in{'passwd'},$crypt_key);
			$changerest{$in{'username'}} = $in{'change_quant'};
			$change_amount{$in{'username'}} = $in{'change_amount'};
			
			#ウェルカム通知
			if ($usenotification) {
				commit_mention($in{'username'},$notifymsg0);
			}
			$phase = 'sanka';
			&store_session_table;
			
			#クッキーを発行
			$c_username = $in{'username'};
			$c_passwd = $passwd{$in{'username'}};
			&set_cookie;
			
			&html_header;
			print<<"_EOF_";
<hr>
<h3>スタートしました</h3>
<table>
<tr><td><b>Username</b></td><td>$in{'username'}</td></tr>
<tr><td><b>パスワード</b></td><td>$in{'passwd'}</td></tr>
<tr><td><b>人数</b></td><td>$in{'ninzuu'}〜$in{'ninzuu_max'}</td></tr>
<tr><td><b>枚数</b></td><td>$in{'maisuu'}</td></tr>
<tr><td><b>札の交換</b></td><td>$in{'change_quant'}回まで$in{'change_amount'}枚以内</td></tr>
</table><br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
			&html_footer;
		}
	}
}

sub mode_pastlog {
	my($nextlog,$prevlog);
	if ($in{'num'} eq "") {
		$in{'num'} = 0;
	}
	
	$nextlog = $in{'num'}+1;
	$prevlog = $in{'num'}-1;
	
	&html_header;
	print "<hr>\n";
	if (-e "$g_pastlogdir/$g_kaitoufile.$nextlog") {
		print "<a href=\"$g_script?mode=pastlog&num=$nextlog\">[←もっと古い記録] </a>";
	}
	if ((-e "$g_pastlogdir/$g_kaitoufile.$prevlog") and ($prevlog >= 0)) {
		print "<a href=\"$g_script?mode=pastlog&num=$prevlog\"> [もっと新しい記録→]</a>";
	}
	print "<br><hr>\n";
	
	print_kekka($in{'num'});
	print<<"_EOF_";
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	&html_footer;
}

sub mode_join {
	my(@wordnumber);
	
	if ($in{'username'} eq '') { &error("名前を入力してください。"); }
	if (grep($_ eq $in{'username'},@members)) {
		&error("その名前の人は既にいます。");
	}
	if ($in{'passwd'} eq "") { &error("パスワードを設定して下さい。"); }
	
	if ($phase eq 'sanka') {
		#メンバーに追加
		push(@members,$in{'username'});
		$passwd{$in{'username'}} = crypt($in{'passwd'},$crypt_key);
		$changerest{$in{'username'}} = $session{'change_quant'};
		$change_amount{$in{'username'}} = $session{'change_amount'};
		
		#通常の参加
		#人数が集まったなら投稿モードへ移行
		if (@members >= $session{'ninzuu'}) {
			#札を全員に配る
			&supply_stock;
			$phase = 'toukou';
			#人数集まりましたmentionを投げる
			if ($usenotification) {
				foreach (@members) {
					if ( $_ ne $in{'username'} ) {
						commit_mention($_,$notifymsg1);
					}
				}
			}
		}
	}
	elsif (($phase eq 'toukou') and (@members < $session{'ninzuu_max'}) ) {
		#メンバーに追加
		push(@members,$in{'username'});
		$passwd{$in{'username'}} = crypt($in{'passwd'},$crypt_key);
		$changerest{$in{'username'}} = $session{'change_quant'};
		$change_amount{$in{'username'}} = $session{'change_amount'};
		
		#途中参加
		#全員の$stockと、kaitou.dat内の解答で使われた数を除外した札を選ぶ
		#もし残りを合わせて$session{'maisuu'}に満たない場合、参加できない
		&load_words_table;
		
		@wordnumber = &get_availablewordlist;
		
		if (@wordnumber < $session{'maisuu'}) {&error("札が不足のため参加できません。");}
		$stock{$in{'username'}} = join(",",splice(@wordnumber,0,$session{'maisuu'}));
	}
	else { &error("参加受付中ではありません。"); }
	
	#ウェルカム通知
	if ($usenotification) {
		commit_mention($in{'username'},$notifymsg0);
	}
	
	#セッション情報をストア
	&store_session_table;
	#クッキーを発行
	$c_username = $in{'username'};
	$c_passwd = $passwd{$in{'username'}};
	&set_cookie;
	&html_header;
	print "<hr>\n";
	print "<h3>参加しました。</h3>\n";
	print "Username：$in{'username'}<br>\n";
	print "パスワード：$in{'passwd'}<br>\n";
	print "<a href=\"$g_script\" target=_top>[戻る]</a>\n";
	&html_footer;
}

sub mode_repaircookie
{
	if (($in{'passwd'} eq "") or ($in{'username'} eq "")) {
		&html_header;
		print<<"_EOF_";
<hr>
<h3>クッキーの再発行</h3>
Usernameと、参加時のパスワードを入力してください。<br>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="repaircookie">
Username：<input type="text" name="username" value="">
パスワード：<input type="password" name="passwd" value="">
<input type="submit" name="submit" value="再発行"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
<hr>
_EOF_
	}
	else {
		if ($passwd{$in{'username'}} eq "") {&error("$in{'username'}さんは参加していません。");}
		if (crypt($in{'passwd'},$crypt_key) ne $passwd{$in{'username'}}) {
			&error("パスワードが違います。");
		}
		$c_username = $in{'username'};
		$c_passwd = $passwd{$in{'username'}};
		&set_cookie;
		&html_header;
		print<<"_EOF_";
<hr>
<h3>クッキーの再発行</h3>
クッキーが再発行されました。<br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	}
	&html_footer;
}


sub mode_joincancel {
	if ($phase ne 'sanka') { &error("参加受付中ではありません。"); }
	&get_cookie;
	#cookieのパスワードを照合する
	if ($c_passwd ne $passwd{$c_username}) {&error("参加していません。");}
	#リーダーかどうか調べる
	if ($c_username eq $members[0]) {&error("開始した人はキャンセルできません。");}
	
	#メンバーリストから取り除く
	@members = grep($_ ne $c_username,@members);
	
	&store_session_table;
	
	#$c_username = "";
	$c_passwd = "";
	&set_cookie;	
	
	&html_header;
	print<<"_EOF_";
<hr>
<h3>参加取り消し</h3>
$c_usernameさんの参加を取り消しました。<br><br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	
	&html_footer;
}


#札を取り替える
sub mode_change {
	my($ansnum,%count,@wordnumber,$word);
	local(@anslist,@stocklist);
	if ($phase ne 'toukou') { &error("現在解答を受け付けていません"); }
	&get_cookie;
	if ($changerest{$c_username} eq 0) {&error("取り替え回数が残っていません");}
	if ($c_passwd ne $passwd{$c_username}) {&error("参加していません。");}
	#持ち札があるか
	if ($stock{$c_username} eq "") {&error("$c_usernameさんの持ち札はありません");}
	@stocklist = split(/,/,$stock{$c_username});
	#内容があるか
	if ($in{'changelist'} eq "") {&error("入力されていません。");}
	@anslist = split(/,/,$in{'changelist'});
	#数字以外が入ってないか
	if (grep(/\D/,@anslist)) {&error("コンマと数字のみを入力してください。");}
	#交換可能枚数を超えていないか
	if (@anslist > $change_amount{$c_username}) {&error("交換できる枚数を超えています。");}
	#存在しない札を入力していないか
	&load_words_table;
	foreach (@anslist) {
		if ($_ >= $totalwords) {&error("存在しない札を入力しています。");}
	}
	#持っていない札を入力していないか
	foreach (@anslist) {
		$ansnum = $_;
		if (grep($_ eq $ansnum,@stocklist) eq 0) {
			&error("持っていない札が入力されています。");
		}
	}
	#同じものを２枚以上出していないか
	foreach (@anslist) {
		if (++$count{$_} > 1) { &error("同じ札を２枚以上入力しています。"); }
	}
	
	if ($in{'confirm'}) {
		&html_header;
		print<<"_EOF_";
<hr>
<h3>札を交換</h3>
_EOF_
		foreach (@anslist) {
			$word = $words[$_];
			print "$_：$word<br>\n";
		}
		print "<br>を捨ててもよろしいですか？<br><br>\n";
		
		print<<"_EOF_";
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="change">
<input type="hidden" name="confirm" value="0">
<input type="hidden" name="changelist" value="$in{'changelist'}">
<input type="submit" name="submit" value="ＯＫ"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		&html_footer;
	}
	else {
		@wordnumber = &get_availablewordlist;
		
		#残り札が交換希望枚数より少ない時はある分だけ取り替える
		if ($#wordnumber > $#anslist) {	$#wordnumber = $#anslist; }
		else { $#anslist = $#wordnumber; }
		#捨てた札をストックから削除
		foreach (@anslist) {
			$ansnum = $_;
			@stocklist = grep($ansnum ne $_,@stocklist);
		}
		#新しい札を加える
		push(@stocklist,@wordnumber);
		#新しい札データを書き込む
		$stock{$c_username} = join(",",@stocklist);
		
		$changerest{$c_username}--;
		$change_amount{$c_username} = $change_amount{$c_username} - @anslist;
		&store_session_table;
		
		&html_header;
		print<<"_EOF_";
<hr>
<h3>札を交換</h3>
_EOF_
	
		foreach (@anslist) {
			$word = $words[$_];
			print "$_：$word<br>\n";
		}
		print "<br>を捨てて<br><br>\n";
		foreach (@wordnumber) {
			$word = $words[$_];
			print "$_：$word<br>\n";
		}
		print "<br>を入手しました。<br><br>\n";
		
		print<<"_EOF_";
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		&html_footer;
	}
}

sub mode_answer {
	my($ansnum,%count,$sentence);
	local(@anslist,@stocklist);
	
	#--エラーチェック--
	if ($phase ne 'toukou') { &error("現在解答を受け付けていません。"); }
	&get_cookie;
	if ($c_passwd ne $passwd{$c_username}) {&error("参加していません。");}
	#持ち札があるか
	if ($stock{$c_username} eq "") {&error("$c_usernameさんの持ち札はありません");}
	@stocklist = split(/,/,$stock{$c_username});
	#内容があるか
	if ($in{'answer'} eq "") {&error("解答が入力されていません。");}
	@anslist = split(/,/,$in{'answer'});
	#数字以外が入ってないか
	if (grep(/\D/,@anslist)) {&error("コンマと数字のみを入力してください。");}
	#２枚以上使っているか
	if ($#anslist eq 0) {&error("２枚以上使ってください。");}
	#存在しない札を入力していないか
	&load_words_table;
	foreach (@anslist) {
		if ($_ >= $totalwords) {&error("存在しない札を入力しています。");}
	}
	#持っていない札を入力していないか
	foreach (@anslist) {
		$ansnum = $_;
		if (grep($_ eq $ansnum,@stocklist) eq 0) {
			&error("持っていない札が入力されています。");
		}
	}
	#同じものを２枚以上使っていないか
	foreach (@anslist) {
		if (++$count{$_} > 1) { &error("同じ札が２枚以上使われています。"); }
	}
	
	if ($in{'confirm'}) {
		$sentence = numlist2sentence(@anslist);
		&html_header;
		print<<"_EOF_";
<hr>
<h3>解答の確認</h3>
<b>$sentence</b><br><br>
を登録してもよろしいですか？<br>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="answer">
<input type="hidden" name="answer" value="$in{'answer'}">
<input type="submit" name="submit" value="登録OK"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	&html_footer;
	}
	else {
	#解答を登録
	$in{'answer'} = join(",",@anslist);
	&get_time;
	open OUT, ">> $g_kaitoufile";
	print OUT "0<>$in{'answer'}<>$c_username<>$date\n";
	close OUT;
	
	#使った札をストックから削除
	foreach (@anslist) {
		$ansnum = $_;
		@stocklist = grep($ansnum ne $_,@stocklist);
	}
	$stock{$c_username} = join(",",@stocklist);
	
	#全員の解答が終了したか調べてモード移行を行う
	$remain = 0;
	foreach (@members) {
		if ($stock{$_}) {$remain++;}
	}
	if ($remain eq 0) {
		$phase = 'kekka';
		if ($usenotification) {
			foreach (@members) {
				if ( $_ ne $c_username ) {
					commit_mention($_,$notifymsg2);
				}
			}
		}
	}
	&store_session_table;
	
	$sentence = numlist2sentence(@anslist);
	&html_header;
	print<<"_EOF_";
<hr>
<h3>解答を登録</h3>
<b>$sentence</b><br><br>
を登録しました。<br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	&html_footer;
	}
}

sub mode_giveup {
	my($remain);
	
	if ($phase ne 'toukou') { &error("現在解答を受け付けていません。"); }
	&get_cookie;
	if ($c_passwd ne $passwd{$c_username}) {&error("参加していません。");}
	#持ち札があるか
	if ($stock{$c_username} eq "") {&error("$c_usernameさんの解答は終了しています");}

	if ($in{'confirm'}) {
		&html_header;
		print<<"_EOF_";
<hr>
<h3>解答終了</h3>
以後解答できなくなりますが、よろしいですか？<br>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="giveup">
<input type="hidden" name="confirm" value="0">
<input type="submit" name="submit" value="ＯＫ"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		&html_footer;
	}
	else {
		#持ち札を空にする
		$stock{$c_username} = "";
		
		#全員の解答が終了したか調べてモード移行を行う
		$remain = 0;
		foreach (@members) {
			if ($stock{$_}) {$remain++;}
		}
		if ($remain eq 0) {
			$phase = 'kekka';
			if ($usenotification) {
				foreach (@members) {
					if ( $_ ne $c_username ) {
						commit_mention($_,$notifymsg2);
					}
				}
			}
		}
		&store_session_table;
		
		&html_header;
		print<<"_EOF_";
<hr>
<h3>解答終了</h3>
$c_usernameさんの解答を終了しました。<br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		&html_footer;
	}
}

sub mode_vote {
	my(@kaitou,$foot,$hyousuu,$answer,@anslist,$sentence,@comments);
	if ($phase ne 'kekka') { &error("現在投票を受け付けていません。"); }
	if (grep(/\D/,$in{'ansnum'})) {&error("投票は数値を指定してください。");}

	if (($in{'comment'} eq "") or ($in{'comentator'} eq "")) {
		#解答ファイル中の得票数をインクリメントする
		open FH, "+< $g_kaitoufile";
		@kaitou = <FH>;
		
		if ($in{'ansnum'} > $#kaitou) {&error("範囲外の解答を指定しています。");}
		
		($hyousuu,$answer,$foot) = split(/<>/,$kaitou[$in{'ansnum'}],3);
		$hyousuu = $hyousuu + $in{'increment'};
		$kaitou[$in{'ansnum'}] = "$hyousuu<>$answer<>$foot";
		seek FH,0,0;
		print FH @kaitou;
		close FH;
		
		&get_cookie;
		if ($c_passwd ne $passwd{$c_username}) {$c_username = "";}
		
		@anslist = split(/,/,$answer);
		&load_words_table;
		$sentence = numlist2sentence(@anslist);
		
		&html_header;
		if ($in{'increment'}) {
			print<<"_EOF_";
<hr>
<h3>$sentenceに投票しました</h3>
投票ありがとうございます。<br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
		}
		&html_footer;
		
	}
}

sub mode_help {
	my($helptext);
	
	if ($phase eq 'kekka') {
		$helptext =<<"_EOF_";
■ランダムに配られた言葉を組み合わせて面白げな文を作るゲームです。<br>
■投票＆コメント機能\付きです。<br>
■始めるにはまずある程度、言葉を集める必要があります。<br>
■札が少なすぎると開始できないことがあります。<br>
■面白そうだと思った言葉を「札を追加」からどんどん入力してください。<br>
■ただし、あまり狙い過ぎた言葉からは面白い文が出来難いです、注意。<br>
■つまらない言葉同士で意外に面白い文が出来たりもします。<br>
■つなげ難い言葉というのもあります。<br>
■なるべくどんな言葉にもつながるような言葉を入れていくのがコツです。<br>
■ゲームを始めるには「新しく始める」をクリックして参加者を募集してください。<br>
■募集人数、配る単語数等を一人目の参加者が決めます。<br>
■Usernameとパスワードがcookieに保存されます。<br>
■cookieはパスワードを入力することで再発行ができます。<br>
_EOF_
	}
	elsif ($phase eq 'sanka') {
		$helptext =<<"_EOF_";
■ランダムに配られた言葉を組み合わせて面白げな文を作るゲームです。<br>
■投票＆コメント機能\付きです。<br>
■面白そうだと思った言葉を「札を追加」からどんどん入力してください。<br>
■ただし、あまり狙い過ぎた言葉からは面白い文が出来難いです、注意。<br>
■つまらない言葉同士で意外に面白い文が出来たりもします。<br>
■つなげ難い言葉というのもあります。<br>
■なるべくどんな言葉にもつながるような言葉を入れていくのがコツです。<br>
■名前と好きなパスワードを入力することでどなたでも参加できます。<br>
■Usernameとパスワードがcookieに保存されます。<br>
■所定の人数が参加すると参加者は解答できるようになります。<br>
■入っている単語の中から、重複しないようにシャッフルされ、<br>
　参加者に配分されます。<br>
■もし参加しているのに参加フォームが現れる場合は、cookieの再発行をしてください。<br>
_EOF_
	}
	elsif ($phase eq 'toukou') {
		$helptext =<<"_EOF_";
■ランダムに配られた言葉を組み合わせて面白げな文を作るゲームです。<br>
■投票＆コメント機能\付きです。<br>
■面白そうだと思った言葉を「札を追加」からどんどん入力してください。<br>
■ただし、あまり狙い過ぎた言葉からは面白い文が出来難いです、注意。<br>
■つまらない言葉同士で意外に面白い文が出来たりもします。<br>
■つなげ難い言葉というのもあります。<br>
■なるべくどんな言葉にもつながるような言葉を入れていくのがコツです。<br>
■配られた単語を自由に使って文を作る事が出来ます。<br>
■解答は半角数字で入力してください。<br>
■参加者の方には、既に解答した解答が表\示されますが、<br>
　全員が解答するまで、他の人には一切見えません。<br>
■開始した人が途中参加を許可していれば、全員の解答が揃う前でなおかつ、<br>
　残りの札があれば、解答中でも参加が可能\です。<br>
_EOF_
	}
	&html_header;
	print<<"_EOF_";
<hr>
<h3>使い方</h3>
<table border=0>
<tr><td>
$helptext
</td></tr>
</table><br>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	&html_footer;
}

sub mode_addword {
	my(@filedata,$today,$yday,$newword);
	
	if ($in{'word'} eq '') {
		&error("文字が入っていないぞ？");
	}
	$newword = $in{'word'};
	
	#データベースに接続
	my $dbh = DBI->connect("DBI:mysql:$g_database", $g_dbuser, $g_dbpassword)
	or &error("Can't execute : $DBI::errstr");
	$dbh->do("SET NAMES utf8") or &error("Can't execute : $DBI::errstr");
	
	#以前に同じ単語が入れられていないかチェック
	if (!$in{'forceadd'}) {
		$result = $dbh->prepare("SELECT word FROM words WHERE word = \'$newword\';") or &error("Can't execute : $DBI::errstr");
		$result->execute() or &error("Can't execute : $DBI::errstr");
		my $found = $result->rows;
		$result->finish() or &error("Can't execute : $DBI::errstr");
		
		if ( $found > 0 ) {
			&html_header;
			print<<"_EOF_";
<hr>
<h3>“$in{'word'}”は既にありますが追加しますか？</h3>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="addword">
<input type="hidden" name="word" value="$in{'word'}">
<input type="hidden" name="forceadd" value="1">
<input type="submit" name="submit" value="追加する"><br>
</form>
<a href="$g_script" target=_top>[やめる]</a>
_EOF_
			&html_footer;
			#データベースを切断
			$dbh->disconnect();
			return;
		}
	}
	
	#単語を書き込む
	$result = $dbh->do("INSERT INTO words (word, date) VALUES ('$newword', NOW());")
	or &error("Can't execute : $DBI::errstr");

	#最大保持数を超えたら古い順に削除する
#	while (((@filedata-1)>$g_maxwords) and ($g_maxwords ne 0)) {
#		splice(@filedata,1,1);
#	}
	
	&html_header;
	print<<"_EOF_";
<hr>
<h3>“$in{'word'}”を追加しました</h3>
<br>
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="addword">
さらに追加
<input type="text" name="word" value="">
<input type="submit" name="submit" value="追加"><br>
</form>
<a href="$g_script" target=_top>[戻る]</a>
_EOF_
	&html_footer;
	
	#データベースを切断
	$dbh->disconnect();
}


# オフラインでやりたくなったときのために札をcsv形式で書き出す
sub mode_get {
	local($cols);
	print "Content-type: application/octet-stream\n";
	print "Content-Disposition: attachment; filename=words.csv\n\n";
	
	&load_words_table;
	
	$cols=0;
	foreach (@words) {
		print "$_,";
		$cols = ($cols+1)%4;
		if ($cols == 0) {print "\n";}
	}
}


sub supply_stock {
	my(@wordnumber);
	&load_words_table;
	
	@wordnumber = &get_availablewordlist;
	
	#札を配る
	foreach (@members) {
		$stock{$_} = join(",",splice(@wordnumber,0,$session{'maisuu'}));
	}
}


#使用可能な単語のリストを得る
#要load_words_table
sub get_availablewordlist {
	my(@filedata,@usedlist,$ans,@wordnumber,@rnd);
	
	#使われている札の番号の配列を得る
	foreach (@members) {
		push(@usedlist,split(/,/,$stock{$_}));
	}
	if (-s $g_kaitoufile) {
		open IN, "$g_kaitoufile";
		@filedata = <IN>;
		close IN;
		foreach (@filedata) {
			(undef,$ans) = split(/<>/);
			push(@usedlist,split(/,/,$ans));
		}
	}
	#残っている札番号の配列を得る
	@wordnumber = 0..($totalwords-1);
	foreach (@usedlist) { $wordnumber[$_] = -1; }
	@wordnumber = grep($_ ne -1,@wordnumber);

	#シャッフルする
	while (@wordnumber) {
 	   push(@rnd, splice(@wordnumber , rand @wordnumber , 1));
	}
	return @rnd;
}

sub print_kekka {
	local($pastno) = @_;
	my(@filedata,$past,%kaitouhash,$hyousuu,$ansindex,$kt,$knum,$ans,$kaitousya,@sentence,@commenttext,$comment,$temp);
	
	if ($pastno < 0) {$past = 0;}
	else {$past = 1;}
	
	#解答データの読み込み
	if ($past) {
		open IN, "$g_pastlogdir/$g_kaitoufile.$pastno";
	}
	else  {
		open IN, "$g_kaitoufile";
	}
	@filedata = <IN>;
	close IN;
	$knum=0;
	foreach (@filedata) {
		chomp;
		($hyousuu,$kt) = split(/<>/,$_,2);
		if ($past or $g_kekkasort) {
			$kaitouhash{sprintf("%04d<>%04d",$hyousuu,$knum++)} = $kt;
		}
		else {
			$kaitouhash{sprintf("%04d<>%04d",$knum++,$hyousuu)} = $kt;
		}
	}
	#解答データの表示
	if ($past) {
		if ($pastno eq 0) {
			print "<h2>前回の結果</h2>\n";
		}
		else {
			print "<h2>過去の結果 $pastno</h2>\n";
		}
	}
	else {
		print "<h2>結果発表\</h2>\n";
	}
	print "<table border=0><tr>\n";
	print "<th>作品</th><th>詠み人</th>";
	if (!$past) {
		print "<th>Twitter</th>";
		print "<th>投票</th>";
	}
	print "<th>投票数</th></tr>\n";
	foreach (sort {$b cmp $a} keys %kaitouhash) {
		if ($past or $g_kekkasort) {
			($hyousuu,$ansindex) = split(/<>/);
		}
		else {
			($ansindex,$hyousuu) = split(/<>/);
		}
		$hyousuu = $hyousuu*1.0;
		$ansindex = $ansindex*1.0;
		($ans,$kaitousya) = split(/<>/,$kaitouhash{$_});
		if ($past) {
			$sentence[$ansindex] = $ans;
			print "<tr><th>$sentence[$ansindex]</th><th>$kaitousya</th><th>$hyousuu</th>\n";
		}
		else {
			@anslist = split(/,/,$ans);
			$sentence[$ansindex] = numlist2sentence(@anslist);
			print "<tr><th>$sentence[$ansindex]</th><th>$kaitousya</th>";
			#Twitterにつぶやく
			$tweet_msg = " 『$sentence[$ansindex]』by \@$kaitousya $g_scripturl ";
			$tweet_msg = url_encode($tweet_msg);
			print "<th><a href=\"http://twitter.com/home?status=$tweet_msg\" target=\"_blank\">[つぶやく]</a></th>";
			print "<th>";
			print "<a href=\"$g_script?mode=vote&ansnum=$ansindex&increment=1\">[投票する]</a>";
			print "</th><th>$hyousuu</th>\n";
		}
		print "</tr>\n";
	}
	print "</table>\n";
}

sub print_addwordform {
	print<<"_EOF_";
<form action="$g_script" method="post">
<input type="hidden" name="mode" value="addword">
ロバの耳！みたいな事を吹き込んで下さい。（現在$totalwords単語入っています）<br>
<input type="text" name="word" value="">
<input type="submit" name="submit" value="吹き込む"><br>
_EOF_
	if ($todaywords) {print "今日は$todaywords回";}
	if ($yesterdaywords) {print " 昨日は$yesterdaywords回";}
	if ($todaywords or $yesterdaywords) {print "吹き込まれました。<br>";}
	print "</form>";

}

sub commit_mention {
	local($mlad,$inmsg) = @_;
	my $err = 0;
	my $t = Net::Twitter::Lite->new(%g_consumer_tokens);
	
	# トークンをセットする
	$t->access_token($g_access_token);
	$t->access_token_secret($g_access_token_secret);
	
	# 投稿
	my $notify_msg = "\@$mlad $inmsg";
	my $utf8 = Encode::decode_utf8 $notify_msg;
	my $status = $t->update({ status => $utf8 });
	
	return $err;
}


#  エラー処理
sub error {
#	if ($lfh) { my_funlock($lfh); }

	&html_header if (!$head_flag);
	print "<center><hr><h3>ERROR !</h3>\n";
	print "<p><font color=red>$_[0]</font><br>\n";
	print "<a href=\"$g_script\" target=_top>[戻る]</a>\n";
	print "<p><hr></center></body></html>\n";
	exit;
}

sub load_session_table {
	my($key,$val,$username);
	
	undef(%session);
	undef(@members);
	undef(%passwd);
	undef(%stock);
	undef(%changerest);
	
	#データベースに接続
	my $dbh = DBI->connect("DBI:mysql:$g_database", $g_dbuser, $g_dbpassword)
	or &error("Can't execute : $DBI::errstr");
	$dbh->do("SET NAMES utf8") or &error("Can't execute : $DBI::errstr");

	#セッション情報を読み込む
	my $result = $dbh->prepare("SELECT * FROM session;") or &error("Can't execute : $DBI::errstr");
	$result->execute() or &error("Can't execute : $DBI::errstr");
	my $href = $result->fetchrow_hashref();
	$session{'phase'} = $href->{'phase'};
	$session{'ninzuu'} = $href->{'ninzuu'};
	$session{'ninzuu_max'} = $href->{'ninzuu_max'};
	$session{'maisuu'} = $href->{'maisuu'};
	$session{'change_quant'} = $href->{'change_quant'};
	$session{'change_amount'} = $href->{'change_amount'};
	$result->finish() or &error("Can't execute : $DBI::errstr");
	
	#参加者情報を読み込む
	$result = $dbh->prepare("SELECT * FROM members;") or &error("Can't execute : $DBI::errstr");
	$result->execute() or &error("Can't execute : $DBI::errstr");
	while ( $href = $result->fetchrow_hashref() ) {
		$username = $href->{'username'};
		push(@members, $username);
		$passwd{$username} = $href->{'passwd'};
		$stock{$username} = $href->{'stock'};
		$changerest{$username} = $href->{'changerest'};
		$change_amount{$username} = $href->{'change_amount'};
	}
	$result->finish() or &error("Can't execute : $DBI::errstr");

	#データベースを切断
	$dbh->disconnect();
}


sub store_session_table {
	#データベースに接続
	my $dbh = DBI->connect("DBI:mysql:$g_database", $g_dbuser, $g_dbpassword)
	or &error("Can't execute : $DBI::errstr");
	$dbh->do("SET NAMES utf8") or &error("Can't execute : $DBI::errstr");
	
	#セッション情報をクリアする
	my $result = $dbh->do("DELETE FROM session;") or &error("Can't execute : $DBI::errstr");
	
	#セッション情報を書き込む
	$result = $dbh->do("INSERT INTO session VALUES(
	'',
	'',
	'$phase',
	'$session{'ninzuu'}',
	'$session{'ninzuu_max'}',
	'$session{'maisuu'}',
	'$session{'change_quant'}',
	'$session{'change_amount'}'
	);") or &error("Can't execute : $DBI::errstr");
	
	#参加者情報情報をクリアする
	my $result = $dbh->do("DELETE FROM members;") or &error("Can't execute : $DBI::errstr");
	
	#参加者情報を書き込む
	foreach (@members) {
		$result = $dbh->do("INSERT INTO members VALUES(
		'$_',
		'$passwd{$_}',
		'$stock{$_}',
		'$changerest{$_}',
		'$change_amount{$_}'
		);") or &error("Can't execute : $DBI::errstr");
	}
	
	#データベースを切断
	$dbh->disconnect();
}


sub load_words_table {
	my(@href,@filedata);
	if (!defined(@words)) {
		#データベースに接続
		my $dbh = DBI->connect("DBI:mysql:$g_database", $g_dbuser, $g_dbpassword)
		or &error("Can't execute : $DBI::errstr");
		$dbh->do("SET NAMES utf8") or &error("Can't execute : $DBI::errstr");
		
		#単語を読み込む
		$result = $dbh->prepare("SELECT word FROM words;") or &error("Can't execute : $DBI::errstr");
		$result->execute() or &error("Can't execute : $DBI::errstr");
		
		@words = ();
		while ( @href = $result->fetchrow_array() ) {
			push( @words, $href[0] );
		}
		$result->finish() or &error("Can't execute : $DBI::errstr");
		$totalwords=@words;
		
		#今日追加された単語数を取得する
		$result = $dbh->prepare("SELECT word FROM words WHERE TO_DAYS( NOW() ) = TO_DAYS( date );") or &error("Can't execute : $DBI::errstr");
		$result->execute() or &error("Can't execute : $DBI::errstr");
		$todaywords = $result->rows;
		$result->finish() or &error("Can't execute : $DBI::errstr");

		#昨日追加された単語数を取得する
		$result = $dbh->prepare("SELECT word FROM words WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1;") or &error("Can't execute : $DBI::errstr");
		$result->execute() or &error("Can't execute : $DBI::errstr");
		$yesterdaywords = $result->rows;
		$result->finish() or &error("Can't execute : $DBI::errstr");

		#データベースを切断
		$dbh->disconnect();
	}
}


sub refresh_kaitou_table {
	my(@filedata,@anslist,$name,$ans,$dat,$hyousuu,$sentence,$hyoumax,$numlogs,$oldnum);
	open FH, "+< $g_kaitoufile";
	@filedata = <FH>;
	truncate FH,0;
	close FH;
	
	#過去ログのファイル名をひとつずつ送る
	for ($numlogs=0; -e "$g_pastlogdir/$g_kaitoufile.$numlogs"; $numlogs++) {}
	for (; $numlogs>0; $numlogs--) {
		$oldnum = $numlogs-1;
		rename("$g_pastlogdir/$g_kaitoufile.$oldnum","$g_pastlogdir/$g_kaitoufile.$numlogs");
		rename("$g_pastlogdir/$commentfile.$oldnum","$g_pastlogdir/$commentfile.$numlogs");
	}
	
	&load_words_table;
	open OUT, "> $g_pastlogdir/$g_kaitoufile.0";
	$hyoumax = 0;
	foreach (@filedata) {
		($hyousuu,$ans,$name,$dat) = split(/<>/);
		@anslist = split(/,/,$ans);
		$sentence = numlist2sentence(@anslist);
		print OUT "$hyousuu<>$sentence<>$name\n";
		if ($hyoumax <= $hyousuu) {
			#前回の最高票をキーに使う
			$hyoumax = $hyousuu;
		}
	}
	close OUT;
	
	open IN, "+< $commentfile";
	@filedata = <IN>;
	truncate IN,0;
	close IN;
	open OUT, "> $g_pastlogdir/$commentfile.0";
	print OUT @filedata;
	close OUT;
}

sub numlist2sentence {
	local(@numlist) = @_;
	if (!defined(@words)) { &load_words_table; }
	my @listwords;
	foreach (@numlist) {
		my $sent = $words[$_];
		push(@listwords,$sent);
	}
	return join(" ",@listwords);
}

#  日付文字列を取得
sub get_time {
	$ENV{'TZ'} = "JST-9";
	$times = time;
	local($sec,$min,$hour,$mday,$mon,$year,$wday) = localtime($times);
	@week = ('日','月','火','水','木','金','土');

	# 日時のフォーマット
	$date = sprintf("%04d/%02d/%02d(%s) %02d:%02d",
			$year+1900,$mon+1,$mday,$week[$wday],$hour,$min);
}

sub set_cookie {
	# クッキーは60日間有効
	local($sec,$min,$hour,$mday,$mon,$year,$wday) = gmtime(time+60*24*60*60);
	@month=('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	@week = ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
	$gmt = sprintf("%s, %02d-%s-%04d %02d:%02d:%02d GMT",
		$week[$wday],$mday,$month[$mon],$year+1900,$hour,$min,$sec);
	$cook=url_encode("username<>$c_username\,passwd<>$c_passwd");
	print "Set-Cookie: ojcgi=$cook; expires=$gmt;\n";
}

sub get_cookie {
	local %GET = $cgi->parse_cookies;
	local @pairs = split(/,/, $GET{'ojcgi'});
	foreach (@pairs) {
		local($key,$val) = split(/<>/);
		$COOK{$key} = $val;
	}
	$c_username  = $COOK{'username'};
	$c_passwd = $COOK{'passwd'};
	if ($c_passwd eq "") {$c_passwd = 'empty';}
}
