<?php

require_once 'globals.php';
require_once 'common.php';

$session = array();
$json_data = array();
$json_data['error'] = 0;

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

//ログインしてなかったらエラー
if ( is_login() == false ) {
	$json_data['error'] = 1;
	$json_data['status'] = 'ログインしていません。';
	write_json_result( $json_data );
}

load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//--エラーチェック--
if ( $session['phase'] != 'toukou' ) {
	$json_data['error'] = 1;
	$json_data['status'] = '現在解答を受け付けていません。';
	write_json_result( $json_data );
}
if ( in_array($c_username, $members) == FALSE ) {
	$json_data['error'] = 1;
	$json_data['status'] = '参加していません。';
	write_json_result( $json_data );
}
//持ち札があるか
if ($stock[$c_username] == '') {
	$json_data['error'] = 1;
	$json_data['status'] = $c_username . 'さんの持ち札はありません';
	write_json_result( $json_data );
}
$stocklist = explode(',', $stock[$c_username] );
//内容があるか
if ( $in['answer'] == '') {
	$json_data['error'] = 1;
	$json_data['status'] = '解答が入力されていません。';
	write_json_result( $json_data );
}
$anslist = explode( ',', $in['answer'] );
//数字以外が入ってないか
foreach ( $anslist as $ansnum ) {
	if ( ctype_digit( $ansnum ) == FALSE ) {
		$json_data['error'] = 1;
		$json_data['status'] = 'コンマと数字のみを入力してください。';
		write_json_result( $json_data );
	}
}
//２枚以上使っているか
if ( count( $anslist ) < 2 ) {
	$json_data['error'] = 1;
	$json_data['status'] = '２枚以上使ってください。';
	write_json_result( $json_data );
}
//存在しない札を入力していないか
$words = array();
$totalwords = load_words_table( $link, $words );
foreach ( $anslist as $ansnum ) {
	if ( $ansnum >= $totalwords ) {
		$json_data['error'] = 1;
		$json_data['status'] = '存在しない札を入力しています。';
		write_json_result( $json_data );
	}
}
//持っていない札を入力していないか
foreach ( $anslist as $ansnum ) {
	if ( in_array( $ansnum, $stocklist ) == FALSE ) {
		$json_data['error'] = 1;
		$json_data['status'] = '持っていない札が入力されています。';
		write_json_result( $json_data );
	}
}
//同じものを２枚以上出していないか
foreach ( $anslist as $ansnum ) {
	$count = 0;
	foreach ( $anslist as $ansnum1 ) {
		if ( $ansnum == $ansnum1 ) {
			$count++;
			if ( $count >= 2 ) {
				$json_data['error'] = 1;
				$json_data['status'] = '同じ札を２枚以上入力しています。';
				write_json_result( $json_data );
			}
		}
	}
}

$sentence = numlist2sentence( $anslist, $words );

$is_last = '';

//ページを表示
if ( isset($in['confirm']) && ($in['confirm']!=0) ) {
	//確認メッセージを出力
	$json_data['error'] = 0;
	$json_data['status'] = "『 $sentence 』でよろしいですか？";
	write_json_result( $json_data );
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
		//交換回数を０にする
		$changerest[$c_username] = 0;
		$change_amount[$c_username] = 0;
		
		$session['phase'] = 'kekka';
		if ( $usenotification2 && count($members) > 1 ) {
			$is_last = 1;
			$_SESSION['is_last'] = 1;
		}
		//解答をログへ移動
		$table_name = push_kaitou_table_pastlog( $link, $kaitou_table_name );
		$kaitou_table_name = $table_name;
	}
	store_session_table( $link, $session );
	store_members( $link, $members, $stock, $changerest, $change_amount );
	
	if ( $is_last ) {
		$pagetitle = '解答の確認';
		$smarty->assign( 'pagetitle', $pagetitle );
		
		$message = '全員の解答が終わりました';
		$smarty->assign( 'message', $message );
		
		$default_msg = $notifymsg2;
		$post_msg = $g_scripturl . '?p=' . $session['session_key'];
		//投稿用トークン生成
		$post_token = generate_post_token();
		$_SESSION['post_token'] = $post_token;
	
		$to = array();
		foreach ( $members as $memb ) {
			if ( $memb != $c_username ) {
				$to[] = $memb;
			}
		}
		
		$smarty->assign( 'default_msg', $default_msg );
		$smarty->assign( 'post_msg', $post_msg );
		$smarty->assign( 'post_token', $post_token );
		$smarty->assign( 'to', $to );
		$g_js_url[] = 'js/jquery.js';
		$smarty->assign( 'g_js_url', $g_js_url );
		$smarty->display( $g_tpl_path . 'page_send_mention.tpl' );
	}
	else {
		header('Location: ' . $g_scripturl);
	}
}
