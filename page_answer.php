<?php

require_once 'globals.php';
require_once 'common.php';

$session = array();

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

//ログインしてなかったらtopに飛ぶ
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//--エラーチェック--
if ( $session['phase'] != 'toukou' ) {
	error("現在解答を受け付けていません。");
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
if ( $in['answer'] == '') {
	error("解答が入力されていません。");
}
$anslist = explode( ',', $in['answer'] );
//数字以外が入ってないか
foreach ( $anslist as $ansnum ) {
	if ( ctype_digit( $ansnum ) == FALSE ) {
		error("コンマと数字のみを入力してください。");
	}
}
//２枚以上使っているか
if ( count( $anslist ) < 2 ) {
	error("２枚以上使ってください。");
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

$sentence = numlist2sentence( $anslist, $words );

//ページを表示
if ( isset($in['confirm']) ) {
	//確認ページを表示
	$smarty->assign( 'in', $in );
	$smarty->assign( 'sentence', $sentence );
	$smarty->display( $g_tpl_path . 'page_answer_confirm.tpl' );
}
else {
	//解答を登録
	$in['answer'] = implode(',',$anslist);
	
	//登録されている回答数を取得する
	$sql = sprintf( "SELECT id FROM %s", $kaitou_table_name );
	$query = mysql_query( $sql, $link );
	$kaitou_total = mysql_num_rows( $query );
	
	//解答を追加
	$sql = sprintf( "INSERT INTO %s ( id, content, wordlist, author, date, votes) VALUES ( %d, '%s', '%s', '%s', NOW(), 0 );"
	,$kaitou_table_name
	,$kaitou_total
	,$sentence
	,$in['answer']
	,$c_username
	);
	$query = mysql_query( $sql, $link );
	
	//使った札をストックから削除
	$stocklist = array_diff( $stocklist, $anslist );
	$stock[$c_username] = implode(',', $stocklist);
	
	//全員の解答が終了したか調べてモード遷移を行う
	$remain = 0;
	foreach ( $members as $memb ) {
		if ( $stock[$memb] > 0 ) {
			$remain++;
		}
	}
	if ( $remain == 0 ) {
		$session['phase'] = 'kekka';
		if ( $usenotification ) {
			foreach ( $members as $memb ) {
				if ( $memb !== $c_username ) {
					commit_mention( $memb, $notifymsg2 );
				}
			}
		}
		//解答をログへ移動
		$table_name = push_kaitou_table_pastlog( $link, $kaitou_table_name );
		$kaitou_table_name = $table_name;
	}
	store_session_table( $link, $session );
	store_members( $link, $members, $stock, $changerest, $change_amount );
	
	$smarty->assign( 'sentence', $sentence );
	$smarty->display( $g_tpl_path . 'page_answer.tpl' );
}

//データベースを切断
mysql_close( $link );
