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

//ログインしてなかったらtopに飛ぶ
if ( is_login() == false ) {
	$json_data['error'] = 1;
	$json_data['status'] = 'ログインしていません。';
	write_json_result( $json_data );
}

load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

if ( $session['phase'] != 'toukou' ) {
	$json_data['error'] = 1;
	$json_data['status'] = '現在解答を受け付けていません。';
	write_json_result( $json_data );
}
if ( $changerest[$c_username] == 0 ) {
	$json_data['error'] = 1;
	$json_data['status'] = '取り替え回数が残っていません、';
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
if ( $in['changelist'] == '') {
	$json_data['error'] = 1;
	$json_data['status'] = '入力されていません。';
	write_json_result( $json_data );
}
$anslist = explode( ',', $in['changelist'] );

//数字以外が入ってないか
foreach ( $anslist as $ansnum ) {
	if ( ctype_digit( $ansnum ) == FALSE ) {
		$json_data['error'] = 1;
		$json_data['status'] = 'コンマと数字のみを入力してください。';
		write_json_result( $json_data );
	}
}
//交換可能枚数を超えていないか
if ( count($anslist) > $change_amount[$c_username] ) {
	$json_data['error'] = 1;
	$json_data['status'] = '交換できる枚数を超えています。';
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

//ページを表示
if ( isset($in['confirm']) && ($in['confirm']!=0) ) {
	$disp_list = '';
	foreach ( $anslist as $ansnum ) {
		$disp_list = $disp_list . '『' . $words[$ansnum] . '』';
	}

	//確認メッセージを出力
	$json_data['error'] = 0;
	$json_data['status'] = "$disp_list を捨ててもよろしいですか？";
	write_json_result( $json_data );
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
	$out_list = '';
	foreach ( $anslist as $ansnum ) {
		$out_list = $out_list . '『' . $words[$ansnum] . '』';
	}
	$in_list = '';
	foreach ( $wordnumber as $ansnum ) {
		$in_list = $in_list . '『' . $words[$ansnum] . '』';
	}
	
	//確認メッセージを出力
	$json_data['error'] = 0;
	$json_data['status'] = "$in_list を入手しました。";
	write_json_result( $json_data );
}
