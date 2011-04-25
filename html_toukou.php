<?php

require_once 'globals.php';
require_once 'common.php';

//oj.phpから続く

$table_name = sprintf( '%s_0', $pastlog_table_name );
$is_exist_pastlog = is_exist_table( $link, $table_name );

$words = array();
$totalwords = load_words_table( $link, $words );

load_members( $link, $members, $stock, $changerest, $change_amount );

//参加者名を取得
$c_username = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//ヘッダー
$pagetitle = '解答受付中';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );

//参加者一覧表示
write_members_html( $members, $stock, $c_username );

?>
<script type="text/javascript">
if(!Array.indexOf) {
	Array.prototype.indexOf = function(o)
	{
		for(var i in this) {
			if(this[i] == o) {
			return i;
			}
		}
		return -1;
	}
}
function reset_input() {
	document.answer_form.answer.value = '';
	$("#ans_input").empty();
	return false;
}
function submit_input() {
	if ( document.answer_form.answer.value.length > 0 ) {
		document.answer_form.submit();
	}
	return false;
}
function input_word( word,scr_word ) {
	var word_str = String(word);
	var ans = document.answer_form.answer.value;
	var update = true;
	if ( ans.length == 0 ) {
		ans = word_str;
	}
	else {
		ans = ans + ',' + word_str;
		scr_word = ' ' + scr_word;
		var ans_array = document.answer_form.answer.value.split(',');
		var match = ans_array.indexOf(word_str);
		if ( match > -1 ) {
			update = false;
		}
	}
	if ( update ) {
		document.answer_form.answer.value = ans;
		$("#ans_input").append(scr_word);
	}
	return false;
}
function reset_change() {
	document.change_form.changelist.value = '';
	$("#change_input").empty();
	return false;
}
function submit_change() {
	if ( document.change_form.changelist.value.length > 0 ) {
		document.change_form.submit();
	}
	return false;
}
function input_changeword( word,scr_word ) {
	var word_str = String(word);
	var change = document.change_form.changelist.value;
	var update = true;
	if ( change.length == 0 ) {
		change = word_str;
	}
	else {
		change = change + ',' + word_str;
		var change_array = document.change_form.changelist.value.split(',');
		var match = change_array.indexOf(word_str);
		if ( match > -1 ) {
			update = false;
		}
	}
	if ( update ) {
		document.change_form.changelist.value = change;
		scr_word = '<span class="change_word">' + scr_word + '</span>';
		$("#change_input").append(scr_word);
	}
	return false;
}
</script>
<div id="content_right">
<?php
if ( in_array($c_username, $members) ) {
	//参加者である
	
	//解答したものを表示
	$sql = sprintf( "SELECT content FROM %s WHERE author = '%s'", $kaitou_table_name, $c_username );
	$query = mysql_query( $sql, $link );
	$answers = array();
	while ( $row = @mysql_fetch_array( $query, MYSQL_NUM ) ) {
		array_push( $answers, $row[0] );
	}
	$smarty->assign( 'c_username', $c_username );
	$smarty->assign( 'answers', $answers );
	$smarty->display( $g_tpl_path . 'html_answers.tpl' );
	
	if ( $stock[$c_username] !== '' ) {
		//持ち札の一覧を表示
		$stock_array = explode( ',', $stock[$c_username] );
		$word_array = array();
		foreach ( $stock_array as $num ) {
			array_push( $word_array, $words[$num] );
		}
		$smarty->assign( 'stock_array', $stock_array );
		$smarty->assign( 'word_array', $word_array );
		$smarty->assign( 'g_giveup_confirm', $g_giveup_confirm );
		$smarty->display( $g_tpl_path . 'html_answer_form.tpl' );
	}
	else {
		echo "<p>$c_username さんはもう解答できません。</p>";
	}
	
}
else {
	//参加者以外
	if ( count( $members ) < $session['ninzuu_max'] ) {
		echo '<p>途中参加受付中！</p>';
		$smarty->display( $g_tpl_path . 'html_sanka_form.tpl' );
	}
}

//交換回数が残っている
if ( $changerest[$c_username] > 0 && $change_amount[$c_username] ) {
	$smarty->assign( 'c_rest', $changerest[$c_username] );
	$smarty->assign( 'c_amount', $change_amount[$c_username] );
	$smarty->display( $g_tpl_path . 'html_change_form.tpl' );
}

if ( $allow_addword ) {
	$todaywords = get_todaywords( $link );
	$yesterdaywords = get_yesterdaywords( $link );

	//単語を追加フォーム
	$smarty->assign( 'totalwords', $totalwords );
	$smarty->assign( 'todaywords', $todaywords );
	$smarty->assign( 'yesterdaywords', $yesterdaywords );
	$smarty->display( $g_tpl_path . 'html_addwordform.tpl' );
}

echo '</div><div id="pre_footer">';

if ( $c_username == $session['leadername'] ) {
	echo '<a href="page_start_confirm.php?p=' . $session['session_key'] . '">[始めからやる]</a>';
}

//このページへのリンク
write_urltweet( $g_scripturl, $session['session_key'] );

//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php">[過去ログ]</a>';
}

echo '</div>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
