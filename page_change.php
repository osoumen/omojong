<?php

require_once 'globals.php';
require_once 'common.php';

$session = array();

//データベースに接続
$link = connect_db();

$session = load_session_table( $link );

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$c_username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';


if ( $session['phase'] != 'toukou' ) {
	error("現在解答を受け付けていません");
}
if ( $changerest[$c_username] == 0 ) {
	error("取り替え回数が残っていません");
}
if ( in_array($c_username, $members) == FALSE ) {
	error("参加していません。");
}
//持ち札があるか
if ($stock[$c_username] == '') {
	error( $c_username . 'さんの持ち札はありません');
}
$stocklist = explode(',', $stock[$c_username] );
//内容があるか
if ( $in['changelist'] == '') {
	error("入力されていません。");
}
$anslist = explode( ',', $in['changelist'] );

//数字以外が入ってないか
foreach ( $anslist as $ansnum ) {
	if ( ctype_digit( $ansnum ) == FALSE ) {
		error("コンマと数字のみを入力してください。");
	}
}
//交換可能枚数を超えていないか
if ( count($anslist) > $change_amount[$c_username] ) {
	error("交換できる枚数を超えています。");
}
//存在しない札を入力していないか
$words = array();
$totalwords = load_words_table( $link, $words );
foreach ( $anslist as $ansnum ) {
	if ( $ansnum >= $totalwords ) {
		error("存在しない札を入力しています。");
	}
}
//持っていない札を入力していないか
foreach ( $anslist as $ansnum ) {
	if ( in_array( $ansnum, $stocklist ) == FALSE ) {
		error("持っていない札が入力されています。");
	}
}
//同じものを２枚以上出していないか
foreach ( $anslist as $ansnum ) {
	$count = 0;
	foreach ( $anslist as $ansnum1 ) {
		if ( $ansnum == $ansnum1 ) {
			$count++;
			if ( $count >= 2 ) {
				error("同じ札を２枚以上入力しています。");
			}
		}
	}
}

//ページを表示
if ( isset($in['confirm']) ) {
	$disp_list = array();
	foreach ( $anslist as $ansnum ) {
		$disp_list[$ansnum] = $words[$ansnum];
	}
	//確認ページを表示
	$smarty->assign( 'in', $in );
	$smarty->assign( 'disp_list', $disp_list );
	$smarty->display( $g_tpl_path . 'page_change_confirm.tpl' );
}
else {
	$wordnumber = get_availablewordlist( $link, $members, $stock, $totalwords );
	
	//残り札が交換希望枚数より少ない時はある分だけ取り替える
	if ( count($wordnumber) > count($anslist) ) {
		$wordnumber = array_slice( $wordnumber, 0, count( $anslist ) );
	}
	else {
		$anslist = array_slice( $anslist, 0, count( $wordnumber ) );
	}
	//捨てた札をストックから削除
	$stocklist = array_diff( $stocklist, $anslist );

	//新しい札を加える
	$stocklist = array_merge( $stocklist, $wordnumber);
	//新しい札データを書き込む
	$stock[$c_username] = implode(',', $stocklist);
	
	$changerest[$c_username]--;
	$change_amount[$c_username] = $change_amount[$c_username] - count( $anslist );
	store_members( $link, $members, $stock, $changerest, $change_amount );
	
	//配列に格納
	$out_list = array();
	foreach ( $anslist as $ansnum ) {
		$out_list[$ansnum] = $words[$ansnum];
	}
	$in_list = array();
	foreach ( $wordnumber as $ansnum ) {
		$in_list[$ansnum] = $words[$ansnum];
	}
	
	//受領ページを表示
	$smarty->assign( 'in_list', $in_list );
	$smarty->assign( 'out_list', $out_list );
	$smarty->display( $g_tpl_path . 'page_change.tpl' );
}

//データベースを切断
mysql_close( $link );
