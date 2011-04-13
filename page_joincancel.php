<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

if ($session['phase'] != 'sanka') {
	error("参加受付中ではありません。");
}

$c_username = $_COOKIE['username'];

//ユーザー名を照合する
$members = array();
$stock = array();
$changerest = array();
$change_amount = array();
load_members( $link, $members, $stock, $changerest, $change_amount );
if ( in_array($c_username, $members) == FALSE ) {
	error( $c_username . 'さんは参加していません。' );
}

//リーダーかどうか調べる
if ($c_username == $session['leadername']) {
	error("開始した人はキャンセルできません。");
}

//メンバーリストから取り除く
$sql = sprintf( "DELETE FROM %s WHERE username = '%s';", $members_table_name, $c_username );
$query = mysql_query( $sql, $link );

//cookieの削除
setcookie( 'username', '', time() - 3600, '/' );

//データベースを切断
mysql_close( $link );

message( '参加取り消し', $c_username . 'さんの参加を取り消しました。' );