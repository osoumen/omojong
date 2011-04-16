<?php

$g_title = '作文ゲーム';		// タイトル
$g_script = './oj.php';

$g_scripturl = 'http://www.benjamin-lab.com/~ojbot/oj.php';

$g_kekkasort = 0;			//結果発表を投票数順に表示する（0なら投稿順）

$g_start_confirm = 1;		//開始の確認画面を表示する？(1=YES 0=NO)
$g_answer_confirm = 1;	//投稿の確認画面を表示する？(1=YES 0=NO)
$g_giveup_confirm = 1;	//投了の確認画面を表示する？(1=YES 0=NO)

$g_maxwords = 0;			//保持する単語の最大数　超えると古いものから消えていく　０だと無制限

//スタイルシートの設定
$g_css_url = 'css/default.css';

define('G_DATABASE', 'omojong');
define('CONSUMER_KEY', 'oVHQOYjXkfrEOGEVdRosQ');
define('CONSUMER_SECRET', '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8');
define('ACCESS_TOKEN', '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT');
define('ACCESS_TOKEN_SECRET', '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg');

//mentionによる通知を使用する(1=YES 0=NO)
$usenotification = 0;

//Twitter以外の単語追加をありにするか
//1にすると、スタート時に単語がリセットされない
$allow_addword = 0;

//参加したときの通知の内容
$notifymsg0 = "ご参加ありがとうございます！まだまだ参加受付中です。 ($g_title $g_scripturl)";

//参加人数が集まったときの通知の内容
$notifymsg1 = "参加人数が集まりました。解答受付中です！ ($g_title $g_scripturl)";

//解答が終わったときの通知の内容
$notifymsg2 = "解答が出揃いました。結果を見られます！ ($g_title $g_scripturl)";

//Smarty関係
define('SMARTY_DIR', '/usr/local/lib/smarty/');
define('SMARTY_TEMP', '/var/www/smarty/');
require_once(SMARTY_DIR . 'Smarty.class.php');

//Smartyのインスタンスを作成
$smarty = new Smarty();

//各ディレクトリの指定
$smarty->template_dir = SMARTY_TEMP . 'templates';
$smarty->compile_dir = SMARTY_TEMP . 'templates_c';
$smarty->config_dir = SMARTY_TEMP . 'configs';
$smarty->cache_dir = SMARTY_TEMP . 'cache';

$smarty->default_modifiers = array('escape');

//キャッシュ機能の有効化
//$smarty->caching = true;

$g_tpl_path = 'tpl/';
$smarty->assign( 'header_path', 'tpl/header.tpl' );
$smarty->assign( 'footer_path', 'tpl/footer.tpl' );

$smarty->assign( 'g_title', $g_title );
$smarty->assign( 'g_css_url', $g_css_url );
$smarty->assign( 'g_script', $g_script );
$smarty->assign( 'g_scripturl', $g_scripturl );

$smarty->assign( 'g_start_confirm', $g_start_confirm );
$smarty->assign( 'g_answer_confirm', $g_answer_confirm );
$smarty->assign( 'g_giveup_confirm', $g_giveup_confirm );

//各データへのテーブル名
$words_table_name = 'words';
$members_table_name = 'members';
$kaitou_table_name = 'kaitou';
$pastlog_table_name = 'pastlog';

//ゲームIDのパラメーター名
$gameid_param_name = 'p';
