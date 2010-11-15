<?php

$g_title = '面雀(おもじゃん)2.0';		# タイトル
$g_script = './oj.cgi';				#このファイル自身（705）

$g_scripturl = 'http://benjamin-lab.com/~ojbot/oj.cgi';

$g_kekkasort = 0;			#結果発表を投票数順に表示する（0なら投稿順）

$g_start_confirm = 1;		#開始の確認画面を表示する？(1=YES 0=NO)
$g_answer_confirm = 1;	#投稿の確認画面を表示する？(1=YES 0=NO)
$g_giveup_confirm = 1;	#投了の確認画面を表示する？(1=YES 0=NO)

$g_maxwords = 0;			#保持する単語の最大数　超えると古いものから消えていく　０だと無制限

#スタイルシートの設定
$g_css_url = 'css/default.css';

$g_database = 'omojong';
$g_dbuser = 'ojbot';
$g_dbpassword = 'korogottu';

#mentionによる通知を使用する(1=YES 0=NO)
$usenotification = 0;

#Twitter関連
$consumer_key    = 'oVHQOYjXkfrEOGEVdRosQ';
$consumer_secret = '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8';
$access_token        = '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT';
$access_token_secret = '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg';

#参加したときの通知の内容
$notifymsg0 = "ご参加ありがとうございます！まだまだ参加受付中です。 ($g_title $g_scripturl)";

#参加人数が集まったときの通知の内容
$notifymsg1 = "参加人数が集まりました。解答受付中です！ ($g_title $g_scripturl)";

#解答が終わったときの通知の内容
$notifymsg2 = "解答が出揃いました。結果を見られます！ ($g_title $g_scripturl)";

$session = array();
$members = array();
$stock = array();
$changerest = array();
$words = array();

