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

if ( isset( $in['as_values_members'] ) ) {
	$in['members'] = $in['as_values_members'];
}

$player_name = $_SESSION['access_token']['screen_name'];

if ( ($session['phase'] == 'sanka' || $session['phase'] == 'toukou') && $player_name != $session['leadername'] ) {
	error('開始者以外は中断できません。');
}

//$totalwords = load_words_table( $link, $words );

//初期値
if ( !isset($in['members']) ) {
	$in['members'] = '';
}
if ( !isset($in['ninzuu']) ) {
	$in['ninzuu'] = '3';
}
if ( !isset($in['ninzuu_max']) ) {
	$in['ninzuu_max'] = '100';
}
if ( !isset($in['maisuu']) ) {
	$in['maisuu'] = '12';
}
if ( !isset($in['change_quant']) ) {
	$in['change_quant'] = '3';
}
if ( !isset($in['change_amount']) ) {
	$in['change_amount'] = '8';
}
if ( !isset($in['end_date']) ) {
	$in['end_date'] = date("Y-m-d", time() + 60 * 60 * 24 * 3);	//デフォルトは3日後
}
if ( !isset($in['end_hour']) ) {
	$in['end_hour'] = '0';
}
$in['members'] = htmlspecialchars($in['members'], ENT_QUOTES);

//エラーチェック
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
elseif ( strptime($in['end_date'], "%Y-%m-%d") == FALSE ) {
	$err_str = '日付のフォーマットが間違っています。';
	unset( $in['end_date'] );
}
elseif (ctype_digit($in['end_hour']) == FALSE) {
	$err_str = '時刻は数値で指定してください。';
	unset( $in['end_hour'] );
}
elseif ($in['end_hour'] > 23 || $in['end_hour'] < 0) {
	$err_str = '時刻の範囲が間違っています。';
	unset( $in['end_hour'] );
}
else {
	//未来の日付かどうかをチェック
	$date = strptime($in['end_date'] . ' ' . $in['end_hour'] . ':00:00', "%Y-%m-%d %H:%M:%S");
	$timestamp = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']-100);
	if ( $timestamp < time() ) {
		$err_str = '現在の時刻以降の期限を指定して下さい。';
	}
}
if ( isset( $in['allow_disclose'] ) ) {
	if ( ctype_digit($in['allow_disclose']) == FALSE ) {
		$err_str = '入力値が範囲外です。';
	}
}
if ( isset( $in['friends_only'] ) ) {
	if ( ctype_digit($in['friends_only']) == FALSE ) {
		$err_str = '入力値が範囲外です。2';
	}
}
$init_members_temp = explode( ',', $in['members'] );
$init_members = array();
foreach ( $init_members_temp as $memb ) {
	//指定したメンバーが全員自分をフォローしているかチェック
	if ( $memb ) {
		if ( !in_array($memb, $init_members) ) {
			$is_follower = is_follower( $memb, $player_name );
			if ( !$is_follower ) {
				$err_str = $memb.'さんはあなたをフォローしていません。';
				break;
			}
			if ( $is_follower === 'error' ) {
				$err_str = '指定されたメンバーは追加できません。';
				break;
			}
			$init_members[] = $memb;
		}
	}
}

//初見もしくは、設定値にエラーがある場合
if ( isset($in['confirm']) == FALSE || $err_str != '' ) {
	//$in['username'] = $player_name;
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
	
	$end_date_options = array();
	for ( $days=0; $days<8; $days++ ) {
		$in_date = date("Y-m-d", time() + 60 * 60 * 24 * $days);
		$end_date_options[$in_date] = date("Y年m月d日", time() + 60 * 60 * 24 * $days);
	}
	
	$end_hour_options = array(
		'0'=>'0:00',
		'1'=>'1:00',
		'2'=>'2:00',
		'3'=>'3:00',
		'4'=>'4:00',
		'5'=>'5:00',
		'6'=>'6:00',
		'7'=>'7:00',
		'8'=>'8:00',
		'9'=>'9:00',
		'10'=>'10:00',
		'11'=>'11:00',
		'12'=>'12:00',
		'13'=>'13:00',
		'14'=>'14:00',
		'15'=>'15:00',
		'16'=>'16:00',
		'17'=>'17:00',
		'18'=>'18:00',
		'19'=>'19:00',
		'20'=>'20:00',
		'21'=>'21:00',
		'22'=>'22:00',
		'23'=>'23:00'
	);
	
	$smarty->assign( 'ninzuu_options', $ninzuu_options );
	$smarty->assign( 'maisuu_options', $maisuu_options );
	$smarty->assign( 'change_quant_options', $change_quant_options );
	$smarty->assign( 'change_amount_options', $change_amount_options );
	$smarty->assign( 'end_date_options', $end_date_options );
	$smarty->assign( 'end_hour_options', $end_hour_options );

	$smarty->display( $g_tpl_path . 'page_start.tpl' );
}
else {
	//入力値に問題が無いので、確認画面を表示する
	if ( $in['confirm'] != 0 ) {
		$date = explode( '-', $in['end_date'] );
		$datetext = sprintf("%4d年%d月%d日 %02d:00",$date[0],$date[1],$date[2],$in['end_hour']);
		
		$pagetitle = '新しく始める';
		$smarty->assign( 'pagetitle', $pagetitle );
		$smarty->assign( 'in', $in );
		$smarty->assign( 'datetext', $datetext );
		$smarty->assign( 'init_members', $init_members );
		$post_msg = ' ' . $g_scripturl . '?p=' . $session['session_key'] . ' by ' . $g_title;
		$smarty->assign( 'post_msg', $post_msg );
		$default_msg = $notifymsg0;
		$smarty->assign( 'default_msg', $default_msg );
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
		//日付を代入
		$session['end_time'] = sprintf("%s %02d:00:00",$in['end_date'],$in['end_hour']);

		//メンバーの初期化
		$members[0] = $player_name;
		foreach ( $init_members as $memb ) {
			if ( $memb ) {
				if ( !in_array($memb, $members) ) {
					$members[] = $memb;
				}
			}
		}
		foreach ( $members as $memb ) {
			$stock[$memb] = '';
			$changerest[$memb] = $in['change_quant'];
			$change_amount[$memb] = $in['change_amount'];
		}
		
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
		foreach ( $members as $memb ) {
			add_word_from_twitter( $link, $words_table_name, $memb );
		}
		
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
		$session['phase'] = 'deal';
		store_session_table( $link, $session );
		store_members( $link, $members, $stock, $changerest, $change_amount );
		
		//クッキーを発行
		setcookie( $gameid_param_name, $session['session_key'], time() + 3600 * 24 * 75 );	//75日有効
		
		//DMでお知らせ
		if ( isset( $in['entry_content'] ) ) {
		$in['entry_content'] = ' ' . $in['entry_content'];
		$post_msg = ' ' . $g_scripturl . '?p=' . $session['session_key'];
		multi_tweet( $init_members, $player_name, $in['entry_content'], $post_msg,
		$_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret'], 1 );
		}

		//ページを表示
		header('Location: ' . $g_scripturl);
		//$pagetitle = '新しく始める';
		//$smarty->assign( 'pagetitle', $pagetitle );
		//$smarty->assign( 'in', $in );
		//$smarty->assign( 'allow_addword', $allow_addword );
		//$smarty->display( $g_tpl_path . 'page_start_success.tpl' );
	}
}
