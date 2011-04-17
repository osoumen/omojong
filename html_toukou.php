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

//過去の記録へのリンク
if ( $is_exist_pastlog ) {
	echo '<a href="page_pastlog.php">[過去ログ]</a>';
}
?>
<table>
<tr><th>参加者</th><th>解答状況</th></tr>
<?php
//参加者一覧表示
foreach ( $members as $memb ) {
	if ($memb === $c_username) {
		$nametext = '<span class="its_me">' . $memb . '</span>';
	}
	else {
		$nametext = $memb;
	}
	if ($stock[$memb] === '') {
		echo "<tr><td>$nametext さん</td><td><font color=blue>解答終了</font></td></tr>\n";
	}
	else {
		echo "<tr><td>$nametext さん</td><td><font color=red>解答中</font></td></tr>\n";
	}
}
?>
</table><br>
<script type="text/javascript">
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
	if ( ans.length == 0 ) {
		ans = word_str;
	}
	else {
		ans = ans + ',' + word_str;
		scr_word = ' ' + scr_word;
	}
	var ans_array = document.answer_form.answer.value.split(',');
	var match = ans_array.indexOf(word_str);
	if ( match == -1 ) {
		$("#ans_input").append(scr_word);
		document.answer_form.answer.value = ans;
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
	if ( change.length == 0 ) {
		change = word_str;
	}
	else {
		change = change + ',' + word_str;
	}
	scr_word = '<span class="change_word">' + scr_word + '</div>';
	var change_array = document.change_form.changelist.value.split(',');
	var match = change_array.indexOf(word_str);
	if ( match == -1 ) {
		$("#change_input").append(scr_word);
		document.change_form.changelist.value = change;
	}
	return false;
}
</script>
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
		$smarty->display( $g_tpl_path . 'html_answer_form.tpl' );
		
		//交換回数が残っている
		if ( $changerest[$c_username] > 0 && $change_amount[$c_username] ) {
			$smarty->assign( 'c_rest', $changerest[$c_username] );
			$smarty->assign( 'c_amount', $change_amount[$c_username] );
			$smarty->display( $g_tpl_path . 'html_change_form.tpl' );
		}
		
		//解答終了ボタン
		echo '<a href="page_giveup.php?confirm=' . $g_giveup_confirm . '">[解答を終了する]</a><br>';
	}
	else {
		echo "<p>$c_username さんはもう解答できません。</p>";
	}
}
else {
	//参加者以外
	if ( count( $members ) < $session['ninzuu_max'] ) {
		echo '<b>途中参加受付中！</b><br>';
		$smarty->display( $g_tpl_path . 'html_sanka_form.tpl' );
	}
}

if ( $c_username == $session['leadername'] ) {
	echo '<a href="page_start_confirm.php?p=' . $session['session_key'] . '">[始めからやる]</a><br>';
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

//このページへのリンク
write_urltweet( $g_scripturl, $session['session_key'] );

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
