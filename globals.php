<?php
//error_reporting(0);

$g_title = 'つぶや記念日(β)';		// タイトル
$g_script = './';

$g_scripturl = 'http://tsubmemo.com/';

$g_kekkasort = 0;			//結果発表を投票数順に表示する（0なら投稿順）

$g_start_confirm = 1;		//開始の確認画面を表示する？(1=YES 0=NO)
$g_answer_confirm = 1;	//投稿の確認画面を表示する？(1=YES 0=NO)
$g_giveup_confirm = 1;	//投了の確認画面を表示する？(1=YES 0=NO)

$g_maxwords = 0;			//保持する単語の最大数　超えると古いものから消えていく　０だと無制限

$g_hot_votes = 5;		//投票がこの数を超えたらtweetする

//データベース名
define('G_DATABASE', 'omojong');

//アプリ固有トークン
define('CONSUMER_KEY', 'oVHQOYjXkfrEOGEVdRosQ');
define('CONSUMER_SECRET', '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8');
//アプリアカウントのアクセストークン
define('ACCESS_TOKEN', '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT');
define('ACCESS_TOKEN_SECRET', '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg');

//mensionによる通知を使用する(1=YES 0=NO)
//$usenotification0 = 0;	//参加時
$usenotification1 = 1;	//最低人数に達した時
$usenotification2 = 1;	//全員が解答を終えた時

$use_useraccount_for_mension = 1;	//通知のときに最後の人のアカウントを使用する

//Twitter以外の単語追加をありにするか
//1にすると、スタート時に単語がリセットされない
$allow_addword = 0;

//ハッシュタグ
$hash_tag = ' #tsubmemo';

//参加したときの通知の内容
$notifymsg0 = '【メンバーに指名しました。参加をお願いします。】';

//参加人数が集まったときの通知の内容
$notifymsg1 = '【人数が集まりました。解答できます！】';

//解答が終わったときの通知の内容
$notifymsg2 = '【解答が締め切られました。結果を見られます！】';

//ユーザーに見せるパラメーター名
$gameid_param_name = 'p';
$pastlog_param_name = 'n';

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
$smarty->assign( 'g_script', $g_script );
$smarty->assign( 'g_scripturl', $g_scripturl );

$smarty->assign( 'g_start_confirm', $g_start_confirm );
$smarty->assign( 'g_answer_confirm', $g_answer_confirm );
$smarty->assign( 'g_giveup_confirm', $g_giveup_confirm );

$smarty->assign( 'gameid_param_name', $gameid_param_name );
$smarty->assign( 'pastlog_param_name', $pastlog_param_name );

//スタイルシート
$g_css_url = array();
$g_css_url[] = 'css/default.css';
/*
$g_css_url[] = 'css/autoSuggest.css';
*/
$smarty->assign( 'g_css_url', $g_css_url );

//Javascript
$g_js_url = array();
/*
$g_js_url[] = 'js/jquery.js';
$g_js_url[] = 'js/mojilen.js';
$g_js_url[] = 'js/jquery.alphanumeric.pack.js';
$g_js_url[] = 'js/jquery.autoSuggest.packed.js';
*/
$smarty->assign( 'g_js_url', $g_js_url );

//各データへのテーブル名
$words_table_name = 'words';
$members_table_name = 'members';
$kaitou_table_name = 'kaitou';
$pastlog_table_name = 'pastlog';

session_start();
$g_screen_name = isset($_SESSION['access_token']['screen_name']) ? htmlspecialchars($_SESSION['access_token']['screen_name']) : '';
$smarty->assign( 'g_screen_name', $g_screen_name );
