<?php

require_once 'globals.php';
require_once 'common.php';

dl('mecab.so');

$session = array();
$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
$words = array();

//データベースに接続
$link = connect_db();

//ゲーム情報をとりあえず読み込もうと試みる
//存在しないp値を入れたらここでエラーとなる
//p値の指定が無かったらNULLを受け取って素通りさせる
$session = load_session_table( $link );

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

$err_str = '';
$in = array_merge( $_POST, $_GET );
$player_name = $_SESSION['access_token']['screen_name'];

if ( ($session['phase'] == 'sanka' || $session['phase'] == 'toukou') && $player_name != $session['leadername'] ) {
	error('開始者以外は中断できません。');
}

//確認時の処理
if ( isset($in['confirm']) ) {
	$totalwords = load_words_table( $link, $words );
	
	if ($in['ninzuu'] == '') {
		$err_str = '人数のパラメータがありません。';
	}
	elseif (ctype_digit($in['ninzuu']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
		unset( $in['ninzuu'] );
	}
	elseif ($in['ninzuu'] < 2) {
		$err_str = '２人以上の人数が必要です。';
	}
	elseif (ctype_digit($in['ninzuu_max']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
		unset( $in['ninzuu_max'] );
	}
	elseif ($in['ninzuu_max'] < $in['ninzuu']) {
		$err_str = '最大人数が最少人数より少ないです。';
	}
	elseif (ctype_digit($in['maisuu']) == FALSE) {
		$err_str = '枚数には数値を指定してください。';
		unset( $in['maisuu'] );
	}
	elseif ($in['maisuu'] < 4) {
		$err_str = '枚数が少なすぎます。';
	}
	elseif (ctype_digit($in['change_quant']) == FALSE) {
		$err_str = '交換可能回数は数値で指定してください。';
		unset( $in['change_quant'] );
	}
	elseif ($in['change_quant'] < 0) {
		$err_str = '交換可能回数の数値が不正です。';
		unset( $in['change_quant'] );
	}
	elseif (ctype_digit($in['change_amount']) == FALSE) {
		$err_str = '交換可能枚数は数値で指定してください。';
		unset( $in['change_amount'] );
	}
	elseif ($in['change_amount'] < 0) {
		$err_str = '交換可能枚数の数値が不正です。';
		unset( $in['change_amount'] );
	}
	if ($in['ninzuu_max'] == '') {
		$in['ninzuu_max'] = $in['ninzuu'];
	}
	if ($in['maisuu'] == '') {
		$in['maisuu'] = 10;
	}
	if ( isset( $in['allow_disclose'] ) ) {
		if ( ctype_digit($in['allow_disclose']) == FALSE ) {
			$err_str = '入力値が範囲外です。';
		}
	}
	if ( isset( $in['friends_only'] ) ) {
		if ( ctype_digit($in['friends_only']) == FALSE ) {
			$err_str = '入力値が範囲外です。';
		}
	}
}

//初見もしくは、設定値にエラーがある場合
if ( isset($in['confirm']) == FALSE || $err_str != '' ) {
	$in['username'] = $player_name;
	if ( !isset($in['ninzuu']) ) {
		$in['ninzuu'] = 3;
	}
	if ( !isset($in['ninzuu_max']) ) {
		$in['ninzuu_max'] = 10;
	}
	if ( !isset($in['maisuu']) ) {
		$in['maisuu'] = 12;
	}
	if ( !isset($in['change_quant']) ) {
		$in['change_quant'] = 3;
	}
	if ( !isset($in['change_amount']) ) {
		$in['change_amount'] = 8;
	}
	if ( !isset($in['allow_disclose']) ) {
		$in['allow_disclose'] = 1;
	}
	
	$pagetitle = '新しく始める';
	$smarty->assign( 'pagetitle', $pagetitle );
	$smarty->assign( 'in', $in );
	$smarty->assign( 'err_str', $err_str );
	
	$ninzuu_options = array(
		'2'=>'２人',
		'3'=>'３人',
		'4'=>'４人',
		'5'=>'５人',
		'6'=>'６人',
		'7'=>'７人',
		'8'=>'８人',
		'9'=>'９人',
		'10'=>'１０人'
	);
		
	$maisuu_options = array(
		'5'=>'５語',
		'8'=>'８語',
		'10'=>'１０語',
		'12'=>'１２語',
		'15'=>'１５語',
		'20'=>'２０語'
	);
	
	$change_quant_options = array(
		'0'=>'交換なし',
		'1'=>'１回まで',
		'2'=>'２回まで',
		'3'=>'３回まで',
		'4'=>'４回まで',
		'5'=>'５回まで'
	);
	
	$change_amount_options = array(
		'0'=>'交換なし',
		'1'=>'１語以内',
		'2'=>'２語以内',
		'3'=>'３語以内',
		'4'=>'４語以内',
		'5'=>'５語以内',
		'6'=>'６語以内',
		'7'=>'７語以内',
		'8'=>'８語以内',
		'9'=>'９語以内',
		'10'=>'１０語以内',
		'11'=>'１１語以内',
		'12'=>'１２語以内',
		'13'=>'１３語以内',
		'14'=>'１４語以内',
		'15'=>'１５語以内',
		'16'=>'１６語以内',
		'17'=>'１７語以内',
		'18'=>'１８語以内',
		'19'=>'１９語以内',
		'20'=>'２０語以内'
	);
	
	$smarty->assign( 'ninzuu_options', $ninzuu_options );
	$smarty->assign( 'maisuu_options', $maisuu_options );
	$smarty->assign( 'change_quant_options', $change_quant_options );
	$smarty->assign( 'change_amount_options', $change_amount_options );

	$smarty->display( $g_tpl_path . 'page_start.tpl' );
}
else {
	//入力値に問題が無いので、確認画面を表示する
	if ( $in['confirm'] != 0 ) {
		$pagetitle = '新しく始める';
		$smarty->assign( 'pagetitle', $pagetitle );
		$smarty->assign( 'in', $in );
		$smarty->display( $g_tpl_path . 'page_start_confirm.tpl' );
	}
	else {
		//確認画面でOKしたので、次の状態に遷移する
		
		//セッション情報の初期化
		if ( $session ) {
			$new_session_key = $session['session_key'];
		}
		else {
			$new_session_key = get_new_session_key( $link, $player_name );
		}
		$session = array();
		$session['leadername'] = $player_name;
		$session['session_key'] = $new_session_key;
		$session['ninzuu'] = $in['ninzuu'];
		$session['ninzuu_max'] = $in['ninzuu_max'];
		$session['maisuu'] = $in['maisuu'];
		$session['change_quant'] = $in['change_quant'];
		$session['change_amount'] = $in['change_amount'];
		if ( isset($in['allow_disclose']) ) {
			$session['allow_disclose'] = $in['allow_disclose'];
		}
		else {
			$session['allow_disclose'] = false;
		}
		if ( isset($in['friends_only']) ) {
			$session['friends_only'] = $in['friends_only'];
		}
		else {
			$session['friends_only'] = false;
		}
		//3日後の日付を代入
		$session['end_time'] = date("Y-m-d H:i:s", time() + (3 * 24 * 60 * 60));

		$members[0] = $player_name;
		$stock[$player_name] = '';
		$changerest[$player_name] = $in['change_quant'];
		$change_amount[$player_name] = $in['change_amount'];
		
		//単語テーブル、参加者テーブル、解答リストテーブル名を決める
		$words_table_name = sprintf( 'words_%s', $new_session_key );
		$members_table_name = sprintf( 'members_%s', $new_session_key );
		$kaitou_table_name = sprintf( 'kaitou_%s', $new_session_key );
	
		$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `%s` (
				word text,
				date date
				)', $words_table_name);
		$query = mysql_query( $sql, $link );
		//単語のリセット
		//if ( $allow_addword == 0 ) {
			$sql = sprintf( 'TRUNCATE `%s`', $words_table_name );
			$query = mysql_query( $sql, $link );
		//}

		$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `%s` (
				username text,
				stock text,
				changerest int,
				change_amount int
				)', $members_table_name);
		$query = mysql_query( $sql, $link );
		
		$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `%s` (
				id int,
				content text,
				wordlist text,
				author text,
				date date,
				votes int
				)', $kaitou_table_name);
		$query = mysql_query( $sql, $link );
		//解答テーブルのリセット
		$sql = sprintf( 'TRUNCATE `%s`', $kaitou_table_name );
		$query = mysql_query( $sql, $link );
		
		//Twitterから単語を取得
		//if ( $allow_addword == 0 ) {
			add_word_from_twitter( $link, $words_table_name );
		//}
		
		//ウェルカム通知
		/*
		if ( $usenotification0 ) {
			if ( $use_useraccount_for_mension ) {
				$error = commit_mention( $player_name, $notifymsg0 . $session['session_key'], $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
			}
			else {
				$error = commit_mention( $player_name, $notifymsg0 . $session['session_key'] );
			}
			if ( $error ) {
				error('Twitterのエラーのため、発言出来ませんでした。('.$error.')');
			}
		}
		*/
		$session['phase'] = 'sanka';
		store_session_table( $link, $session );
		store_members( $link, $members, $stock, $changerest, $change_amount );
		
		//クッキーを発行
		setcookie( $gameid_param_name, $session['session_key'], time() + 3600 * 24 * 75 );	//75日有効
		
		//ページを表示
		header('Location: ' . $g_scripturl);
		//$pagetitle = '新しく始める';
		//$smarty->assign( 'pagetitle', $pagetitle );
		//$smarty->assign( 'in', $in );
		//$smarty->assign( 'allow_addword', $allow_addword );
		//$smarty->display( $g_tpl_path . 'page_start_success.tpl' );
	}
}
