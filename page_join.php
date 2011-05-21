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
	if ( $is_follower == 'error' ) {
		message( '参加', "現在Twitterが利用できません。");
	}
	if ( $is_follower !== true ) {
		message( '参加', $session['leadername'] . "さんのフォロアーのみに制限されています。");
	}
}

if (($phase == 'toukou') and (count($members) < $session['ninzuu_max']) ) {
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
//add_word_from_twitter( $link, $words_table_name );

//セッション情報をストア
$session['phase'] = $phase;
store_session_table( $link, $session );
store_members( $link, $members, $stock, $changerest, $change_amount );

header('Location: ' . $g_scripturl);