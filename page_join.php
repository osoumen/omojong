<?php

require_once 'globals.php';
require_once 'common.php';

dl('mecab.so');

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
$myname = $_SESSION['access_token']['screen_name'];

load_members( $link, $members, $stock, $changerest, $change_amount );

if ( in_array($myname, $members) ) {
	error("既に参加しています。");
}

//開始した人のフォロワーかどうか調べる
if ( $session['friends_only'] ) {
	$is_follower = is_follower( $myname, $session['leadername'] );
	if ( $is_follower !== true ) {
		message( '参加', $session['leadername'] . "さんのフォロアーのみに制限されています。");
	}
}

$is_last = '';

if ($phase == 'sanka') {
	//メンバーに追加
	array_push( $members, $myname );
	$stock[$myname] = '';
	$changerest[$myname] = $session['change_quant'];
	$change_amount[$myname] = $session['change_amount'];
	
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
	$stock[$myname] = implode(',', array_splice($wordnumber, 0, $session['maisuu']));
	
	//メンバーに追加
	array_push( $members, $myname );
	$changerest[$myname] = $session['change_quant'];
	$change_amount[$myname] = $session['change_amount'];
}
else {
	error("現在は参加を受け付けていません。");
}

//Twitterから単語を取得
add_word_from_twitter( $link, $words_table_name );

//セッション情報をストア
$session['phase'] = $phase;
store_session_table( $link, $session );
store_members( $link, $members, $stock, $changerest, $change_amount );

if ( $is_last ) {
	//ページを表示
	$pagetitle = '参加';
	$smarty->assign( 'pagetitle', $pagetitle );
	
	$message = '参加しました';
	$smarty->assign( 'message', $message );

	$default_msg = $notifymsg1;
	$post_msg = $g_scripturl . '?p=' . $session['session_key'];
	//投稿用トークン生成
	$post_token = generate_post_token();
	$_SESSION['post_token'] = $post_token;

	$to = array();
	foreach ( $members as $memb ) {
		if ( $memb != $myname ) {
			$to[] = $memb;
		}
	}
	
	$smarty->assign( 'default_msg', $default_msg );
	$smarty->assign( 'post_msg', $post_msg );
	$smarty->assign( 'post_token', $post_token );
	$smarty->assign( 'to', $to );
	$smarty->display( $g_tpl_path . 'page_send_mention.tpl' );
}
else {
	header('Location: ' . $g_scripturl);
}