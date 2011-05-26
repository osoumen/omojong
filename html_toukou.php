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
$g_css_url[] = 'css/jquery.ui.all.css';
$smarty->assign( 'g_css_url', $g_css_url );
$g_js_url[] = 'js/jquery.js';
$g_js_url[] = 'js/jquery-ui-1.8.13.custom.min.js';
$smarty->assign( 'g_js_url', $g_js_url );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="dialog" title="" style="display : none;">
<div id="dialog_msg"></div>
</div>
<?php
echo '<div id="content_left">';
//参加者一覧表示
write_members_html( $members, $stock, $myname );

echo '<div id="user_navi">';

if ( in_array($myname, $members) ) {
	if ( $stock[$myname] !== '' ) {
		echo '<p><a href="javascript:void(0);" onclick="show_giveup_dialog();">自分の解答を終わる</a></p>';
		echo '<p>解答すると他の人の解答を見られます。</p>';
	}
	else {
		echo "<p>$myname さんはもう解答できません。</p>";
	}
	$end_time = $session['end_time'];
	echo "<p>解答期限</p><p>$end_time</p>";
	
	if ( $myname == $session['leadername'] ) {
		echo '<p><a href="javascript:void(0);" onclick="show_force_end_dialog();">解答を締め切る</a></p>';
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
$(function() {
$('#dialog').dialog({
	bgiframe: true,
	autoOpen: false,
	position: ["center", 200],
	width: 400,
	modal: true
});
});
function show_giveup_dialog() {
	$('#dialog_msg').html('以上で解答を終わりにしますか？');
	$('#dialog').dialog('option',{
	title: '解答の終了',
	buttons: {
		'ＯＫ': function() {
			document.location = 'page_giveup.php';
		},
		'キャンセル': function() {
			$(this).dialog('close');
		}
	}
	});
	$('#dialog').dialog('open');
}
function show_force_end_dialog() {
	$('#dialog_msg').html('全員の解答を締め切ってもよろしいですか？');
	$('#dialog').dialog('option',{
	title: '解答の締め切り',
	buttons: {
		'ＯＫ': function() {
			document.location = 'page_giveup.php?all=1';
		},
		'キャンセル': function() {
			$(this).dialog('close');
		}
	}
	});
	$('#dialog').dialog('open');
}

//IE対策
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
	$('input[name=answer]').val('');
	$("#ans_input").empty();
	return false;
}
function submit_input() {
	if ( $('input[name=answer]').val().length > 0 ) {
		var query = {confirm: 1, answer: ''};
		query['answer'] = $('input[name=answer]').val();
		$.getJSON(
			"page_answer.php",
			query,
			function(json) {
				if ( json['error'] != 0 ) {
					$('#dialog_msg').html(json['status']);
					$('#dialog').dialog('option',{
					title: '解答の確認',
					buttons: {
						'ＯＫ': function() {
							$(this).dialog('close');
						}
					}
					});
					$('#dialog').dialog('open');
				}
				else {
					$('#dialog_msg').html(json['status']);
					$('#dialog').dialog('option',{
					title: '解答の確認',
					buttons: {
						'ＯＫ': function() {
							$(this).dialog('close');
							document.location = "page_answer.php?answer=" + $('input[name=answer]').val();
						},
						'キャンセル': function() {
							$(this).dialog('close');
						}
					}
					});
					$('#dialog').dialog('open');
				}
			}
		);
	}
	return false;
}
function input_word( word,scr_word ) {
	var word_str = String(word);
	var ans = $('input[name=answer]').val();
	var update = true;
	if ( ans.length == 0 ) {
		ans = word_str;
	}
	else {
		ans = ans + ',' + word_str;
		scr_word = ' ' + scr_word;
		var ans_array = $('input[name=answer]').val().split(',');
		var match = ans_array.indexOf(word_str);
		if ( match > -1 ) {
			update = false;
		}
	}
	if ( update ) {
		$('input[name=answer]').val(ans);
		$("#ans_input").append(scr_word);
	}
	return false;
}
function reset_change() {
	$('input[name=changelist]').val('');
	$("#change_input").empty();
	return false;
}
function submit_change() {
	if ( $('input[name=changelist]').val().length > 0 ) {
		var query = {confirm: 1, changelist: ''};
		query['changelist'] = $('input[name=changelist]').val();
		$.getJSON(
			"page_change.php",
			query,
			function(json) {
				if ( json['error'] != 0 ) {
					$('#dialog_msg').html(json['status']);
					$('#dialog').dialog('option',{
					title: 'カードの交換',
					buttons: {
						'ＯＫ': function() {
							$(this).dialog('close');
						}
					}
					});
					$('#dialog').dialog('open');
				}
				else {
					$('#dialog_msg').html(json['status']);
					$('#dialog').dialog('option',{
					title: 'カードの交換',
					buttons: {
						'ＯＫ': function() {
							$(this).dialog('close');
							query['confirm'] = 0;
							$.getJSON(
								"page_change.php",
								query,
								function(json_1) {
									$('#dialog_msg').html(json_1['status']);
									$('#dialog').dialog('option',{
									title: 'カードの交換',
									buttons: {
										'ＯＫ': function() {
											$(this).dialog('close');
											window.location.reload();
										}
									}
									});
									$('#dialog').dialog('open');									
								}
							);
						},
						'キャンセル': function() {
							$(this).dialog('close');
						}
					}
					});
					$('#dialog').dialog('open');
				}
			}
		);
	}
	return false;
}
function input_changeword( word,scr_word ) {
	var word_str = String(word);
	var change = $('input[name=changelist]').val();
	var update = true;
	if ( change.length == 0 ) {
		change = word_str;
	}
	else {
		change = change + ',' + word_str;
		var change_array = $('input[name=changelist]').val().split(',');
		var match = change_array.indexOf(word_str);
		if ( match > -1 ) {
			update = false;
		}
	}
	if ( update ) {
		$('input[name=changelist]').val(change);
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
	echo '<a href="'.$g_script.'?'.$pastlog_param_name.'=new">みんなが作成したコピーを見る</a>';
}

echo '</div>';

//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
