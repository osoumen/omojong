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
$myname = isset($_SESSION['access_token']['screen_name']) ? $_SESSION['access_token']['screen_name'] : '';

//ヘッダー
$pagetitle = '解答中';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );

echo '<div id="content_left">';
//参加者一覧表示
write_members_html( $members, $stock, $myname );

echo '<div id="user_navi">';

if ( in_array($myname, $members) ) {
	if ( $stock[$myname] !== '' ) {
		echo "<a href=\"page_giveup.php?confirm=$g_giveup_confirm\"><p>解答を終わりにする</p></a>";
		echo '<p>終了すると他の人の解答を見られます。</p>';
		$end_time = $session['end_time'];
		echo "<p>解答期限</p><p>$end_time</p>";
	}
	else {
		echo "<p>$myname さんはもう解答できません。</p>";
	}
	
	if ( $myname == $session['leadername'] ) {
		echo "<a href=\"page_giveup.php?confirm=$g_giveup_confirm&all=1\"><p>締め切る</p></a>";
	}
}
else {
	//参加者以外
	write_sanka_navi( $session, $members, $myname );
}
//このページへのリンク
write_urltweet( $g_scripturl, $session['session_key'] );
echo '</div>';

echo '</div>';
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
if ( in_array($myname, $members) ) {
	//参加者である
	
	//解答したものを表示
	foreach ( $members as $memb ) {
		if ( $memb == $myname || $stock[$myname] === '') {
			if ( $memb != $myname && $stock[$memb] !== '' ) {
				continue;
			}
			$sql = sprintf( "SELECT content FROM %s WHERE author = '%s'", $kaitou_table_name, $memb );
			$query = mysql_query( $sql, $link );
			$answers = array();
			while ( $row = @mysql_fetch_array( $query, MYSQL_NUM ) ) {
				array_push( $answers, $row[0] );
			}
			$smarty->assign( 'c_username', $memb );
			$smarty->assign( 'answers', $answers );
			$smarty->display( $g_tpl_path . 'html_answers.tpl' );
		}
	}
	
	if ( $stock[$myname] !== '' ) {
		//持ち札の一覧を表示
		$stock_array = explode( ',', $stock[$myname] );
		$word_array = array();
		foreach ( $stock_array as $num ) {
			array_push( $word_array, $words[$num] );
		}
		$smarty->assign( 'stock_array', $stock_array );
		$smarty->assign( 'word_array', $word_array );
		$smarty->display( $g_tpl_path . 'html_answer_form.tpl' );
	}
		
	//交換回数が残っている
	if ( $changerest[$myname] > 0 && $change_amount[$myname] ) {
		$smarty->assign( 'c_rest', $changerest[$myname] );
		$smarty->assign( 'c_amount', $change_amount[$myname] );
		$smarty->display( $g_tpl_path . 'html_change_form.tpl' );
	}
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

//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="'.$g_script.'?'.$pastlog_param_name.'=new">これまでに作成された文を見る</a>';
}

echo '</div>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
