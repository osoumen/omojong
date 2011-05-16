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
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

load_members( $link, $members, $stock, $changerest, $change_amount );

$err_str = '';
$in = array_merge( $_POST, $_GET );
$myname = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//--エラーチェック--
if ( $session['phase'] !== 'toukou' ) {
	error("現在解答を受け付けていません。");
}
if ( in_array($myname, $members) == FALSE ) {
	error("参加していません。");
}

//リーダーであるか
$all = false;
if ( isset( $in['all'] ) ) {
	if ( $in['all'] == 1 ) {
		//解答〆切出来るのは開始した人だけ
		if ( $myname === $session['leadername'] ) {
			$all = true;
		}
		else {
			error( 'そのページは開くことが出来ません。');
		}
	}
	else {
		error( 'そのページは開くことが出来ません。');
	}
}
else {
	//持ち札があるか
	if ( mb_strlen($stock[$myname]) == 0 ) {
		error( $myname . 'さんの解答は終了しています');
	}
}

$is_last = '';

//ページを表示
if ( isset($in['confirm']) ) {
	if ( $all ) {
		$pagetitle = '解答を締め切る';
		$smarty->assign( 'pagetitle', $pagetitle );
		$smarty->display( $g_tpl_path . 'page_force_end_confirm.tpl' );
	}
	else {
		$pagetitle = '解答の終了';
		$smarty->assign( 'pagetitle', $pagetitle );
		$smarty->display( $g_tpl_path . 'page_giveup_confirm.tpl' );
	}
}
else {
	if ( $all ) {
		foreach ( $members as $memb ) {
			//持ち札を空にする
			$stock[$memb] = '';
			//交換回数を０にする
			$changerest[$memb] = 0;
			$change_amount[$memb] = 0;
		}
	}
	else {
		$stock[$myname] = '';
		$changerest[$myname] = 0;
		$change_amount[$myname] = 0;
	}
	
	//全員の解答が終了したか調べてモード遷移を行う
	$remain = 0;
	foreach ( $members as $memb ) {
		if ( $stock[$memb] ) {
			$remain++;
		}
	}
	if ($remain === 0) {
		$session['phase'] = 'kekka';
		if ( $usenotification2 ) {
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
		$pagetitle = '解答の終了';
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
			if ( $memb != $myname ) {
				$to[] = $memb;
			}
		}
		
		$smarty->assign( 'default_msg', $default_msg );
		$smarty->assign( 'post_msg', $post_msg );
		$smarty->assign( 'post_token', $post_token );
		$smarty->assign( 'to', $to );
		$g_js_url[] = 'js/mojilen.js';
		$smarty->assign( 'g_js_url', $g_js_url );
		$smarty->display( $g_tpl_path . 'page_send_mention.tpl' );
	}
	else {
		header('Location: ' . $g_scripturl);
	}
}
