<?php

require_once 'globals.php';
require_once 'common.php';

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

//phaseがdealでなかったらtopへ
if ( $phase != 'deal' ) {
	header('Location: ' . $g_scripturl);
}

load_members( $link, $members, $stock, $changerest, $change_amount );

//札を全員に配る
$words = array();
$totalwords = load_words_table( $link, $words );

$wordnumber = get_availablewordlist( $link, $members, $stock, $totalwords );
//札を配る
foreach ($members as $memb) {
	$stock[$memb] = implode(',', array_splice( $wordnumber,0, $session['maisuu'] ) );
}

$phase = 'toukou';

//セッション情報をストア
$session['phase'] = $phase;
store_session_table( $link, $session );
store_members( $link, $members, $stock, $changerest, $change_amount );

//topへ飛ぶ
header('Location: ' . $g_scripturl);