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
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

$err_str = '';
$in = array_merge( $_POST, $_GET );
$player_name = $_SESSION['access_token']['screen_name'];

//確認時の処理
if ( isset($in['confirm']) ) {
	$totalwords = load_words_table( $link, $words );
	
	if ($session['phase'] == 'sanka' || $session['phase'] == 'toukou') {
		$err_str = '開催中です。';
	}
//	elseif ($in['username'] == '') {
//		$err_str = '名前を入力してください。';
//	}
	elseif ($in['ninzuu'] == '') {
		$err_str = '人数のパラメータがありません。';
	}
	elseif (ctype_digit($in['ninzuu']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
	}
	elseif ($in['ninzuu'] < 2) {
		$err_str = '２人以上の人数が必要です。';
	}
	elseif (ctype_digit($in['ninzuu_max']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
	}
	elseif ($in['ninzuu_max'] < $in['ninzuu']) {
		$err_str = '最大人数が最少人数より少ないです。';
	}
	elseif (ctype_digit($in['maisuu']) == FALSE) {
		$err_str = '枚数には数値を指定してください。';
	}
	elseif ($in['maisuu'] < 4) {
		$err_str = '枚数が少なすぎます。';
	}
	elseif (ctype_digit($in['change_quant']) == FALSE) {
		$err_str = '交換可能回数は数値で指定してください。';
	}
	elseif ($in['change_quant'] < 0) {
		$err_str = '交換可能回数の数値が不正です。';
	}
	elseif (ctype_digit($in['change_amount']) == FALSE) {
		$err_str = '交換可能枚数は数値で指定してください。';
	}
	elseif ($in['change_amount'] < 0) {
		$err_str = '交換可能枚数の数値が不正です。';
	}
//	elseif ($totalwords < $in['ninzuu']*$in['maisuu']) {
//		$err_str = '札が足りません。';
//	}
	if ($in['ninzuu_max'] == '') {
		$in['ninzuu_max'] = $in['ninzuu'];
	}
	if ($in['maisuu'] == '') {
		$in['maisuu'] = 10;
	}
}

//初見もしくは、設定値にエラーがある場合
if ( isset($in['confirm']) == FALSE || $err_str != '' ) {
	$in['username'] = $player_name;
	if ( !isset($in['ninzuu']) ) {
		$in['ninzuu'] = 2;
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
	
	$smarty->assign( 'in', $in );
	$smarty->assign( 'err_str', $err_str );
	
	$smarty->display( $g_tpl_path . 'page_start.tpl' );
}
else {
	//入力値に問題が無いので、確認画面を表示する
	if ( $in['confirm'] != 0 ) {
		$in['username'] = $player_name;
		$smarty->assign( 'in', $in );
		$smarty->display( $g_tpl_path . 'page_start_confirm.tpl' );
	}
	else {
		//確認画面でOKしたので、次の状態に遷移する
		
		refresh_kaitou_table( $link );
		
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
		if ( $allow_addword == 0 ) {
			$sql = sprintf( 'TRUNCATE `%s`', $words_table_name );
			$query = mysql_query( $sql, $link );
		}

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
		
		//Twitterから単語を取得
		if ( $allow_addword == 0 ) {
			add_word_from_twitter( $link, $words_table_name );
		}
		
		//ウェルカム通知
		if ( $usenotification ) {
			commit_mention( $player_name, $notifymsg0 );
		}
		$session['phase'] = 'sanka';
		store_session_table( $link, $session );
		store_members( $link, $members, $stock, $changerest, $change_amount );
		
		//クッキーを発行
		setcookie( $gameid_param_name, $session['session_key'], time() + 3600 * 24 * 75 );	//75日有効
		
		//ページを表示
		$smarty->assign( 'in', $in );
		$smarty->assign( 'allow_addword', $allow_addword );
		$smarty->display( $g_tpl_path . 'page_start_success.tpl' );
	}
}

//データベースを切断
mysql_close( $link );
