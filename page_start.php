<?php

require_once 'globals.php';

$err_str = '';
$in = $_POST;
$c_username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';

if ( isset($in['new']) == FALSE ) {
	if ($phase != 'kekka') {
		$err_str = '開催中です。';
	}
	elseif ($in['username'] == '') {
		$err_str = '名前を入力してください。';
	}
	elseif ($in['ninzuu'] == '') {
		$err_str = '人数のパラメータがありません。';
	}
	elseif (ctype_digit($in['ninzuu']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
	}
	elseif ($in['ninzuu'] < 2) {
		$err_str = '２人以上の人数が必要です。';
	}
	elseif (ctype_digit($in['ninzuu_max']) == FALSE) {
		$err_str = '人数には数値を指定してください。';
	}
	elseif ($in['ninzuu_max'] < $in['ninzuu']) {
		$err_str = '最大人数が最少人数より少ないです。';
	}
	elseif (ctype_digit($in['maisuu']) == FALSE) {
		$err_str = '枚数には数値を指定してください。';
	}
	elseif ($in['maisuu'] < 4) {
		$err_str = '枚数が少なすぎます。';
	}
	elseif (ctype_digit($in['change_quant']) == FALSE) {
		$err_str = '交換可能回数は数値で指定してください。';
	}
	elseif ($in['change_quant'] < 0) {
		$err_str = '交換可能回数の数値が不正です。';
	}
	elseif (ctype_digit($in['change_amount']) == FALSE) {
		$err_str = '交換可能枚数は数値で指定してください。';
	}
	elseif ($in['change_amount'] < 0) {
		$err_str = '交換可能枚数の数値が不正です。';
	}
	elseif ($totalwords < $in['ninzuu']*$in['maisuu']) {
		$err_str = '札が足りません。';
	}
	if ($in['ninzuu_max'] == '') {
		$in['ninzuu_max'] = $in['ninzuu'];
	}
	if ($in['maisuu'] == '') {
		$in['maisuu'] = 10;
	}
}

if ( isset($in['new']) || $err_str != '' ) {
	if ( !isset($in['username']) ) {
		$in['username'] = $c_username;
	}
	if ( !isset($in['ninzuu']) ) {
		$in['ninzuu'] = 4;
	}
	if ( !isset($in['ninzuu_max']) ) {
		$in['ninzuu_max'] = 10;
	}
	if ( !isset($in['maisuu']) ) {
		$in['maisuu'] = 12;
	}
	if ( !isset($in['change_quant']) ) {
		$in['change_quant'] = 3;
	}
	if ( !isset($in['change_amount']) ) {
		$in['change_amount'] = 8;
	}
	
	$smarty->assign( 'in', $in );
	$smarty->assign( 'err_str', $err_str );
	
	$smarty->display( $g_tpl_path . 'page_start.tpl' );
}
else {
	