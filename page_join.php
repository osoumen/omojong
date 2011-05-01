<?php

require_once 'globals.php';
require_once 'common.php';

dl('mecab.so');

//$in = array_merge( $_POST, $_GET );

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}
$phase = $session['phase'];

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}
$in['username'] = $_SESSION['access_token']['screen_name'];

load_members( $link, $members, $stock, $changerest, $change_amount );

if ( in_array($in['username'], $members) ) {
	error("既に参加しています。");
}

//開始した人のフォロワーかどうか調べる
if ( $session['friends_only'] ) {
	$is_follower = is_follower( $in['username'], $session,
	$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret'] );
	if ( $is_follower !== true ) {
		message( '参加', $session['leadername'] . "さんのフォロアーのみに制限されています。");
	}
}

$is_last = '';

if ($phase == 'sanka') {
	//メンバーに追加
	array_push( $members, $in['username'] );
	$stock[$in['username']] = '';
	$changerest[$in['username']] = $session['change_quant'];
	$change_amount[$in['username']] = $session['change_amount'];
	
	//通常の参加
	//人数が集まったなら札配りモードへ移行、その後投稿モードへ移行
	if ( count($members) >= $session['ninzuu'] ) {
		$phase = 'deal';
		
		//人数集まりましたmentionを投げる
		if ($usenotification1) {
			$is_last = 1;
			$_SESSION['is_last'] = 1;
		}
	}
}
elseif (($phase == 'toukou') and (count($members) < $session['ninzuu_max']) ) {
	//途中参加
	//全員の$stockと、kaitou.dat内の解答で使われた数を除外した札を選ぶ
	//もし残りを合わせて$session['maisuu']に満たない場合、参加できない
	$words = array();
	$totalwords = load_words_table( $link, $words );
	
	$wordnumber = get_availablewordlist( $link, $members, $stock, $totalwords );
	
	if ( count($wordnumber) < 2 ) {
		error("残り単語数が２に満たないので参加できません。");
	}
	$stock[$in['username']] = implode(',', array_splice($wordnumber, 0, $session['maisuu']));
	
	//メンバーに追加
	array_push( $members, $in['username'] );
	$changerest[$in['username']] = $session['change_quant'];
	$change_amount[$in['username']] = $session['change_amount'];
}
else {
	error("現在は参加を受け付けていません。");
}

//Twitterから単語を取得
//if ( $allow_addword == 0 ) {
	add_word_from_twitter( $link, $words_table_name );
//}

//ウェルカム通知
/*
if ($usenotification0) {
	if ( $use_useraccount_for_mension ) {
		$error　=　commit_mention( $in['username'], $notifymsg0 . $session['session_key'], $_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
	}
	else {
		$error　=　commit_mention( $in['username'], $notifymsg0 . $session['session_key'] );
	}
	if ( $error ) {
		error('Twitterのエラーのため、発言出来ませんでした。('.$error.')');
	}
}
*/
//セッション情報をストア
$session['phase'] = $phase;
store_session_table( $link, $session );
store_members( $link, $members, $stock, $changerest, $change_amount );

if ( $is_last ) {
	//ページを表示
	$pagetitle = '参加';
	$smarty->assign( 'pagetitle', $pagetitle );
	$twmsg = $notifymsg1 . $session['session_key'] . $hash_tag;
	$smarty->assign( 'twmsg', $twmsg );
	$smarty->display( $g_tpl_path . 'page_join.tpl' );
}
else {
	header('Location: ' . $g_scripturl);
}