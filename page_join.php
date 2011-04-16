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
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}
$in['username'] = $_SESSION['access_token']['screen_name'];

load_members( $link, $members, $stock, $changerest, $change_amount );

if ( in_array($in['username'], $members) ) {
	error("既に参加しています。");
}

if ($phase == 'sanka') {
	//メンバーに追加
	array_push( $members, $in['username'] );
	$stock[$in['username']] = '';
	$changerest[$in['username']] = $session['change_quant'];
	$change_amount[$in['username']] = $session['change_amount'];
	
	//通常の参加
	//人数が集まったなら投稿モードへ移行
	if ( count($members) >= $session['ninzuu'] ) {
	/*
		//札を全員に配る
		$words = array();
		$totalwords = load_words_table( $link, $words );
		
		$wordnumber = get_availablewordlist( $link, $members, $stock, $totalwords );
		//札を配る
		foreach ($members as $memb) {
			$stock[$memb] = implode(',', array_splice( $wordnumber,0, $session['maisuu'] ) );
		}
	*/
		$phase = 'deal';
		//人数集まりましたmentionを投げる
		if ($usenotification) {
			foreach ( $members as $memb ) {
				if ( $memb != $in['username'] ) {
					commit_mention( $memb, $notifymsg1 );
				}
			}
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
if ($usenotification) {
	commit_mention($in['username'],$notifymsg0);
}

//セッション情報をストア
$session['phase'] = $phase;
store_session_table( $link, $session );
store_members( $link, $members, $stock, $changerest, $change_amount );

//データベースを切断
mysql_close( $link );

//ページを表示
$pagetitle = '参加';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->assign( 'in', $in );
$smarty->assign( 'allow_addword', $allow_addword );
$smarty->display( $g_tpl_path . 'page_join.tpl' );
