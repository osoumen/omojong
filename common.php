<?php

require_once 'twitteroauth.php';
require_once 'globals.php';

function connect_db() {
	$dbServer = 'localhost';
	$g_dbuser = 'ojbot';
	$g_dbpassword = 'korogottu';

	if ( !$link = mysql_connect( $dbServer, $g_dbuser, $g_dbpassword ) ) {
		die('接続できませんでした');
	}
	mysql_select_db( G_DATABASE, $link );
	$sql = "SET NAMES utf8";
	$query = mysql_query( $sql, $link );
	return $link;
}

function is_exist_table( $link, $table_name ) {
	//テーブルの存在チェック
	$sql = sprintf("SHOW TABLES WHERE Tables_in_%s = '$table_name'", G_DATABASE);
	$query = mysql_query( $sql, $link );
	$exists = mysql_num_rows( $query );
	return $exists;
}

function get_disclosed_session_key( $link ) {
	$disclosed_session_key = array();
	
	$sql = 'SELECT * FROM session WHERE friends_only = 0 ORDER BY session_key DESC LIMIT 20';
	$query = mysql_query( $sql, $link );
	if ( $query ) {
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$disclosed_session_key[$row['leadername']] = $row['session_key'];
		}
	}
	return $disclosed_session_key;
}

function get_new_session_key( $link, $leader_name, $update=1 ) {
	$session_key = 99999;
	//セッションテーブル内にleader_nameがあればそのセッションのキーを返す
	$sql = sprintf( "SELECT * FROM session WHERE leadername = %s", mysql_escape_string($leader_name) );
	$query = mysql_query( $sql, $link );
	if ( $query ) {
		$exists = mysql_num_rows( $query );
	}
	else {
		$exists = 0;
	}
	if ( $exists > 0 ) {
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$session_key = $row['session_key'];
		}
	}
	else {
		//無ければ、カウンタを１進めて新しいのを返す
		$sql = sprintf( "SELECT * FROM global" );
		$query = mysql_query( $sql, $link );
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$session_key = $row['total'];
		}
		$session_key++;
		if ( $update ) {
			$sql = "UPDATE global SET total = $session_key";
			$query = mysql_query( $sql, $link );
		}
	}

	return $session_key;
}

function load_session_table( $link ) {
	//GETにパラメータが指定されていればそちらを優先
	//cookieに今回読んだidを記録
	//GETが無い場合は、cookieから取得
	//どちらにも指定が無い場合は、NULLを返す
	//存在しないidを指定された場合はエラー表示

	global $words_table_name;
	global $members_table_name;
	global $kaitou_table_name;
	global $gameid_param_name;
	
	$session = array();
	
	if ( isset($_GET[$gameid_param_name]) ) {
		//GET値が指定されている
		$session_key = $_GET[$gameid_param_name];
	}
	else {
		//cookieに値がある
		if ( isset( $_COOKIE[$gameid_param_name] ) ) {
			$session_key = $_COOKIE[$gameid_param_name];
		}
		else {
			//どちらにもない
			return NULL;
		}
	}
	
	//セッション情報を読み込む
	$session_key = mysql_escape_string( $session_key );
	$sql = sprintf( "SELECT * FROM session WHERE session_key = %s", $session_key );
	$query = mysql_query( $sql, $link );
	if ( !$query || mysql_num_rows( $query ) == 0 ) {
		//cookieを削除してエラー表示
		setcookie( $gameid_param_name, '', time() - 3600 );
		error( '指定されたページは存在しないか、既に消滅しています。' );
		return NULL;
	}
	
	//指定されたidが正しい場合のみcookieに記録する
	setcookie( $gameid_param_name, $session_key, time() + 3600 * 24 * 75 );	//75日有効
	
	$row = @mysql_fetch_array( $query, MYSQL_ASSOC );
	$session['leadername'] = $row['leadername'];
	$session['session_key'] = $row['session_key'];
	$session['phase'] = $row['phase'];
	$session['ninzuu'] = $row['ninzuu'];
	$session['ninzuu_max'] = $row['ninzuu_max'];
	$session['maisuu'] = $row['maisuu'];
	$session['change_quant'] = $row['change_quant'];
	$session['change_amount'] = $row['change_amount'];
	$words_table_name = $row['words_table_name'];
	$members_table_name = $row['members_table_name'];
	$kaitou_table_name = $row['kaitou_table_name'];
	$session['friends_only'] = $row['friends_only'];
	$session['end_time'] = $row['end_time'];
	
	return $session;
}

function initialize_alldata( $link ) {
	$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `global` (
			total int,
			latest_pastlog int
			)');
	$query = mysql_query( $sql, $link );
}

function store_session_table( $link, $session ) {
	global $words_table_name;
	global $members_table_name;
	global $kaitou_table_name;

	//テーブルが無ければ作成する
	$sql = sprintf( 'CREATE TABLE IF NOT EXISTS `session` (
			leadername text,
			session_key text,
			phase text,
			ninzuu int,
			ninzuu_max int,
			maisuu int,
			change_quant int,
			change_amount int,
			words_table_name text,
			members_table_name text,
			kaitou_table_name text,
			friends_only bool,
			end_time datetime
			)');
	$query = mysql_query( $sql, $link );
	
	//セッション情報をクリアする
	$sql = sprintf( "DELETE FROM session WHERE session_key = %s", $session['session_key'] );
	$query = mysql_query( $sql, $link );
	
	//セッション情報を書き込む
	$sql = sprintf( "INSERT INTO session VALUES( '%s', '%s', '%s', %d, %d, %d, %d, %d, '%s', '%s', '%s', %d, '%s' )",
	$session['leadername'],
	$session['session_key'],
	$session['phase'],
	$session['ninzuu'],
	$session['ninzuu_max'],
	$session['maisuu'],
	$session['change_quant'],
	$session['change_amount'],
	$words_table_name,
	$members_table_name,
	$kaitou_table_name,
	$session['friends_only'],
	$session['end_time']
	);
	$query = mysql_query( $sql, $link );
}

function load_members( $link, &$members, &$stock, &$changerest, &$change_amount ) {
	global $members_table_name;
	
	$members = array();
	$stock = array();
	$changerest = array();
	$change_amount = array();

	//参加者情報を読み込む
	$sql = sprintf( "SELECT * FROM %s", $members_table_name );
	$query = mysql_query( $sql, $link );
	while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
		$username = $row['username'];
		array_push($members, $username);
		$stock[$username] = $row['stock'];
		$changerest[$username] = $row['changerest'];
		$change_amount[$username] = $row['change_amount'];
	}
}

function numlist2sentence( $numlist, $words ) {
	$listwords = array();
	foreach ($numlist as $num) {
		$sent = $words[$num];
		$listwords[] = $sent;
	}
	return implode(" ", $listwords);
}

//使用可能な単語のリストを得る
function get_availablewordlist( $link, $members, $stock, $totalwords ) {
	global $kaitou_table_name;
	$usedlist = array();
	
	//使われている札の番号の配列を得る
	foreach ($members as $memb) {
		if ( empty( $stock[$memb] ) == FALSE ) {
			$memb_stock = explode(',', $stock[$memb] );
			foreach ( $memb_stock as $stock_id ) {
				$usedlist[] = $stock_id;
			}
		}
	}

	//投稿されている中に使用された札リストを得る
	$sql = sprintf( "SELECT wordlist FROM %s", $kaitou_table_name );
	$query = mysql_query( $sql, $link );
	while ( $row = mysql_fetch_array( $query, MYSQL_NUM ) ) {
		$ans_used = explode(",", $row[0]);
		foreach ( $ans_used as $stock_id ) {
			$usedlist[] = $stock_id;
		}
	}
	//print_r( $usedlist );
	
	//usedlistを除いた札番号の配列を得る
	$wordnumber = array();
	for ( $i = 0; $i < $totalwords; $i++ ) {
		if ( in_array( $i, $usedlist ) == FALSE) {
			$wordnumber[] = $i;
		}
	}

	//シャッフルする
	shuffle($wordnumber);

	return $wordnumber;
}

function commit_mention($mlad,$inmsg,$access_token=ACCESS_TOKEN,$access_token_secret=ACCESS_TOKEN_SECRET, $is_dm=NULL) {
	$error = '';
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token,$access_token_secret);
	
	// 投稿
	if ( $is_dm ) {
		$notify_msg = 'd ' . $mlad . $inmsg;
	}
	else {
		$notify_msg = '@' . $mlad . $inmsg;
	}
	$req = $to->OAuthRequest("https://twitter.com/statuses/update.json","POST",array("status"=>$notify_msg));
	$xml = json_decode($req);
	if ( isset( $xml->error ) ) {
		$error = $xml->error;
	}
	return $error;
}

function post_tweet($inmsg,$access_token,$access_token_secret) {
	$error = '';
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token,$access_token_secret);
	
	// 投稿
	$notify_msg = $inmsg;	
	$req = $to->OAuthRequest("https://twitter.com/statuses/update.json","POST",array("status"=>$notify_msg));
	$xml = json_decode($req);
	if ( isset( $xml->error ) ) {
		$error = $xml->error;
	}
	return $error;
}

function follow_id($id,$access_token,$access_token_secret) {
	$error = '';
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token,$access_token_secret);
	
	// フォロー
	$req = $to->OAuthRequest("https://api.twitter.com/1/friendships/create.json","POST",array("id"=>$id));
	$xml = json_decode($req);

	if ( isset( $xml->error ) ) {
		$error = $xml->error;
	}
	return $error;
}

function is_follower( $myname, $leadername ) {
	$is_friend = '';
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

	$req = $to->OAuthRequest("http://api.twitter.com/1/friendships/exists.json","GET",array("user_a"=>$myname,"user_b"=>$leadername));
	$xml = (array)json_decode($req);
	if ( isset($xml->error) ) {
		$is_friend = 'error';
	}
	else {
		$is_friend = ($req==='true')?true:false;
	}
	return $is_friend;
}

function store_members( $link, $members, $stock, $changerest, $change_amount ) {
	global $members_table_name;
	
	//参加者情報情報をクリアする
	$sql = sprintf( "DELETE FROM %s", $members_table_name );
	$query = mysql_query( $sql, $link );
	
	//参加者情報を書き込む
	foreach ($members as $memb) {
		$sql = sprintf("INSERT INTO %s VALUES( '%s', '%s', %d, %d )",
		$members_table_name,
		$memb,
		$stock[$memb],
		$changerest[$memb],
		$change_amount[$memb]
		);
		$query = mysql_query( $sql, $link );
	}
}

function load_words_table( $link, &$words ) {
	global $words_table_name;

	if ( count($words) == 0 ) {
		//単語を読み込む
		$sql = sprintf( "SELECT word FROM %s", $words_table_name );
		$query = mysql_query( $sql, $link );
		$words = array();
		if ( $query ) {
			while ( $row = mysql_fetch_array($query, MYSQL_NUM) ) {
				array_push( $words, $row[0] );
			}
		}
	}
	$totalwords = count($words);
	return $totalwords;
}

function get_todaywords( $link ) {
	global $words_table_name;
	//今日追加された単語数を取得する
	$sql = sprintf( "SELECT word FROM %s WHERE TO_DAYS( NOW() ) = TO_DAYS( date )", $words_table_name );
	$query = mysql_query( $sql, $link );
	$todaywords = mysql_num_rows( $query );
	return $todaywords;
}

function get_yesterdaywords( $link ) {
	global $words_table_name;
	//昨日追加された単語数を取得する
	$sql = sprintf( "SELECT word FROM %s WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1", $words_table_name );
	$query = mysql_query( $sql, $link );
	$yesterdaywords = mysql_num_rows( $query );
	return $yesterdaywords;
}

function refresh_kaitou_table( $link ) {
	global $kaitou_table_name;
	$numlogs = -1;
	
	if ( is_exist_table( $link, $kaitou_table_name ) ) {
		//過去ログのファイル名をひとつずつ送る
		for ($numlogs=0; is_exist_table($link, sprintf( "%s_%d", $kaitou_table_name, $numlogs )); $numlogs++) {}
		/*
		for (; $numlogs>0; $numlogs--) {
			$oldnum = $numlogs-1;
			$sql = sprintf( "ALTER TABLE %s_$oldnum RENAME TO %s_$numlogs", $kaitou_table_name, $kaitou_table_name );
			$query = mysql_query( $sql, $link );
		}
		*/
		$sql = sprintf( "ALTER TABLE %s RENAME TO %s_%d", $kaitou_table_name, $kaitou_table_name, $numlogs );
		$query = mysql_query( $sql, $link );
	}
	return $numlogs;
}

function push_kaitou_table_pastlog( $link, $table_name ) {
	global $pastlog_table_name;
	
	$new_table_name = '';
	
	//table_nameが存在しているかチェック
	if ( is_exist_table( $link, $table_name ) ) {
		$sql = sprintf("SELECT * FROM %s", $table_name);
		$query = mysql_query( $sql, $link );
		if ( !$query || mysql_num_rows( $query ) == 0 ) {
			$sql = sprintf("DROP TABLE %s", $table_name);
			$query = mysql_query( $sql, $link );
		}
		else {
			//最近の過去ログの値をインクリメントする
			$sql = 'UPDATE global SET latest_pastlog = latest_pastlog+1';
			$query = mysql_query( $sql, $link );
			
			//インクリメント後の値を取得する
			$sql = sprintf( "SELECT * FROM global" );
			$query = mysql_query( $sql, $link );
			while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
				$numlogs = $row['latest_pastlog'];
			}
			
			//テーブルを過去ログに移動する
			$new_table_name = sprintf( '%s_%d', $pastlog_table_name, $numlogs );
			$sql = sprintf( "ALTER TABLE %s RENAME TO %s", $table_name, $new_table_name );
			$query = mysql_query( $sql, $link );
		}
	}
	//移動後のテーブル名を返す
	return $new_table_name;
}

function error( $msg ) {
	global $smarty;
	global $g_tpl_path;
	global $pagetitle;
	$pagetitle = 'エラー';
	$smarty->assign( 'pagetitle', $pagetitle );
	$smarty->assign( 'err_msg', $msg );
	$smarty->display( $g_tpl_path . 'page_error.tpl' );
	exit();
}

function message( $msg_title, $msg ) {
	global $smarty;
	global $g_tpl_path;
	global $pagetitle;
	$pagetitle = 'メッセージ';
	$smarty->assign( 'pagetitle', $pagetitle );
	$smarty->assign( 'msg_title', $msg_title );
	$smarty->assign( 'msg', $msg );
	$smarty->display( $g_tpl_path . 'page_message.tpl' );
	exit();
}

function is_login() {
	$is_login = true;
	if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
		$is_login = false;
	}
	/*
	else {
		//ログイン情報が正しいかチェック
		$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,
		$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
		$count = 0;
		$req = $to->OAuthRequest("https://twitter.com/statuses/home_timeline.json","GET",array("count"=>$count));
		$xml = json_decode($req);
		if ( isset( $xml->error ) ) {
			$is_login = false;
		}
	}
	*/
	return $is_login;
}

function add_word_from_twitter( $link, $words_table_name, $screen_name=NULL ) {
	//形態素解析エンジンの初期化
	$mecab = new MeCab_Tagger();
	
	// OAuthオブジェクト生成
	$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,
	ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
	//$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
	
	//発言取得
	if ( empty( $screen_name ) ) {
		$screen_name = $_SESSION['access_token']['screen_name'];
	}
	$tw_count = 100;
	//$since = date('r', time() - (75 * 24 * 60 * 60));
	$req = $to->OAuthRequest("https://twitter.com/statuses/user_timeline.json","GET", array('screen_name' => $screen_name, 'count' => $tw_count));
	$xml = json_decode($req);
	
	if ( isset( $xml->error ) ) {
		$error = $xml->error;
		return 0;
	}
	
	$stored_words = array();
	
	//取得発言を解析し、単語抽出
	foreach ($xml as $status) {
		$str = $status->text; // 呟き
		
		//mention,ハッシュタグを削除
		$str = preg_replace('/\@\w+/u', '', $str);
		$str = preg_replace('/#\w+/u', '', $str);
		$str = preg_replace('/RT\W+/u', '', $str);
		
		//括弧で囲まれた部分を削除
		$str = preg_replace('/\(.*\)/u', '', $str);
		
		//URLを削除
		$str = preg_replace('/http:\/\/\S*/u', '', $str);
		
		//空白を削除
		$str = preg_replace('/\s*/u', '', $str);
		
		//形態素解析
		$continued_word = '';
		$prev_midasi = '';
		$prev_hinsi = '';
		$prev_hinsi_d1 = '';
		for ( $node=$mecab->parseToNode($str); $node; $node=$node->getNext() ) {
			if ( $node->getStat() == 2 || $node->getStat() == 3 ) {
				continue;
			}
			$surface = $node->getSurface();
			$feature = explode(',', $node->getFeature() );
			//print $surface . "\t" . $feature[0] . "," . $feature[1] . "\n";
			
			//記号は無視
			//感動詞
			//副詞
			//[接頭詞]+(名詞.*)+[助詞]
			//動詞+[(助動詞.*)]
			//形容詞+[助詞]
			if (
				$feature[0] == '感動詞' ||
				$feature[0] == '副詞' ||
				$feature[0] == '名詞' ||
				$feature[0] == '接頭詞' ||
				$feature[0] == '動詞' ||
				$feature[0] == '形容詞' ||
				$feature[0] == '接頭詞' ||
				(($feature[0] == '助詞')&&(rand(0,5)>0)) ||
				$feature[0] == 'BOS/EOS'
				)
			{
				//-切る-
				//感動詞
				//副詞
				//接頭詞
				//名詞（nextが名詞でない）
				//動詞,(!非自立&!接尾)
				//形容詞,(!非自立&!接尾)
				//接頭詞
				if ( ! (
					( $feature[1] == '非自立' || $feature[1] == '接尾' ) ||
					( $feature[0] == '名詞' && $prev_hinsi == '名詞' ) ||
					( $feature[0] == '名詞' && $prev_hinsi == '接頭詞' ) ) ||
					( $prev_hinsi_d1 == '接尾' )	//直前が接尾なら切る
					)
				{
					if ( $continued_word )
					{
						if (!in_array($continued_word, $stored_words)) {
							$stored_words[] = $continued_word;
						}
						$continued_word = '';
					}
				}
				$continued_word = $continued_word . $surface;
			}
			elseif ( $feature[0] == '助動詞' )
			{
				//-切らない-
				//助詞,助動詞
				//*,(非自立|接尾)
				//名詞（prevが名詞or接頭詞）
				$continued_word = $continued_word . $surface;
			}
			else {
				//句読点で切る
				if ( ( $feature[0] == '記号' && $feature[1] == '句点' ) || ( $feature[0] == '記号' && $feature[1] == '読点' ) )
				{
					if ( $continued_word )
					{
						if (!in_array($continued_word, $stored_words)) {
							$stored_words[] = $continued_word;
						}
						$continued_word = '';
					}
				}
				//-無視-
				//記号
				elseif ( $feature[0] != '記号' ) {
					$continued_word = $continued_word . $surface;
				}
			}
			
			$prev_midasi = $surface;
			$prev_hinsi = $feature[0];
			$prev_hinsi_d1 = $feature[1];
		}
	}
	
	$totalwords = 0;
	foreach ($stored_words as $newword) {	
		//以前に同じ単語が入れられていないかチェック
	//	$sql = sprintf( "SELECT word FROM %s WHERE word = '%s'", $words_table_name, $newword );
	//	$query = mysql_query( $sql, $link );
	//	$found = mysql_num_rows( $query );
		//既にある単語は追加しない
	//	if ( $found == 0 ) {
			//単語をデータベースに書き込む
			$sql = sprintf( "INSERT INTO %s (word, date) VALUES ('%s', NOW())", $words_table_name, $newword );
			$query = mysql_query( $sql, $link );
			if ( $query ) {
				$totalwords++;
			}
	//	}
	}
	return $totalwords;
}

function write_urltweet( $url, $session_key ) {
	$page_url = $url . '?p=' . $session_key;
	echo "<p><a href=\"http://twitter.com/share\" class=\"twitter-share-button\" data-url=\"$page_url\" data-text=\"\" data-count=\"none\" data-related=\"osoumen:作った人\" data-lang=\"ja\">Tweet</a><script type=\"text/javascript\" src=\"http://platform.twitter.com/widgets.js\"></script></p>";
}

function write_members_html( $members, $stock, $myname ) {
	echo '<div class="member">';
	echo '<h4>未解答</h4>';
	echo '<ul>';
	foreach ( $members as $memb ) {
		if ($memb === $myname) {
			$nametext = '<span class="its_me">' . $memb . '</span>';
		}
		else {
			$nametext = $memb;
		}
//		$nametext = "<a href=\"http://twitter.com/$memb\">$nametext</a>";
		if ($stock[$memb] !== '') {
			echo "<li>$nametext</li>\n";
		}
	}
	echo '</ul>';
	echo '<h4>解答済み</h4>';
	echo '<ul>';
	foreach ( $members as $memb ) {
		if ($memb === $myname) {
			$nametext = '<span class="its_me">' . $memb . '</span>';
		}
		else {
			$nametext = $memb;
		}
//		$nametext = "<a href=\"http://twitter.com/$memb\">$nametext</a>";
		if ($stock[$memb] === '') {
			echo "<li>$nametext</li>\n";
		}
	}
	echo '</ul>';
	echo '</div>';
}

function write_members_only_html( $members, $myname, $caption=NULL ) {
	echo '<div class="member">';
	if ( $caption ) {
		echo "<h4>$caption</h4>";
	}
	else {
		echo '<h4>参加中</h4>';
	}
	echo '<ul>';
	foreach ( $members as $memb ) {
		if ($memb === $myname) {
			$nametext = '<span class="its_me">' . $memb . '</span>';
		}
		else {
			$nametext = $memb;
		}
//		$nametext = "<a href=\"http://twitter.com/$memb\">$nametext</a>";
		echo "<li>$nametext</li>\n";
	}
	echo '</ul>';
	echo '</div>';
}

function write_sanka_navi( $session, $members, $myname ) {
	global $gameid_param_name;
	//リーダーをフォローしているかどうか調べる
	$is_follower = true;
	if ( $session['friends_only'] ) {
		$is_follower = is_follower( $myname, $session['leadername'] );
	}
	if ( $is_follower === 'error' ) {
		echo '<p>現在Twitterが利用できません。</p>';
		return;
	}

	if ( $session['phase'] == 'toukou' ) {
		if ( count( $members ) < $session['ninzuu_max'] ) {
			if ( $is_follower ) {
				echo '<a href="page_join.php"><p>途中参加する</p></a>';
			}
			else {
				echo '<p>参加するには<a href="func_follow.php?' . $gameid_param_name . '=';
				echo $session['session_key'] . '">';
				echo $session['leadername'] .'さんをフォロー</a>してください。</p>';
			}
		}
	}
}

function redirect_to_prevpage() {
	global $g_scripturl;
	//直前のページに戻る
	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		//改行コードを削除
		$auth_back_url = str_replace(array("\r\n","\r","\n"), '', $_SERVER['HTTP_REFERER']);
		header('Location: ' . $auth_back_url);
	}
	else {
		header('Location: ' . $g_scripturl);
	}
}

function write_pastlog_nav( $link, $current_num, $pastlog_table_name, $pages=20 ) {
	global $pastlog_param_name;
	global $g_script;
	$minlog = $current_num-round($pages/2);
	$maxlog = $current_num+round($pages/2);
	if ( $minlog < 0 ) {
		$minlog = 0;
	}
	echo '<div id="log_navi">';
	for ($num=$minlog; $num<$maxlog; $num++) {
		$exist = is_exist_table($link, sprintf('%s_%d', $pastlog_table_name, $num) );
		if ( $exist ) {
			if ( $num == $current_num ) {
				echo '['.$num.'] ';
			}
			else {
				echo '<a href="' .$g_script. '?' .$pastlog_param_name. '=' . $num.'">['.$num.'] </a>';
			}
		}
	}
	echo '</div>';
}

function generate_post_token() {
	$seed = $_SERVER['REMOTE_ADDR'] . microtime() . 'aigam';
	$post_token = hash('ripemd160', $seed);
	return $post_token;
}

function is_expired( $datetime ) {
	$date = strptime($datetime, "%Y-%m-%d %H:%M:%S");
	$end_time = mktime($date['tm_hour'],$date['tm_min'],$date['tm_sec'],$date['tm_mon']+1,$date['tm_mday'],$date['tm_year']-100);

	if ( $end_time < time() ) {
		return true;
	}
	else {
		return false;
	}
}

function multi_tweet( $to_array, $myname, $entry_content, $post_msg, $token, $token_secret, $use_dm=NULL ) {
	$error = '';
	foreach ( $to_array as $memb ) {
		if ( $memb != $myname ) {
			//合計文字数140文字をオーバーしていたら本文を縮める
			if ( $use_dm ) {
				$pre = '@';
			}
			else {
				$pre = 'd ';
			}
			$max_len = 140 - mb_strlen( $pre . $memb . $post_msg );
			$inmsg = mb_strimwidth( $entry_content, 0, $max_len, '…' );
			$msg = $inmsg . $post_msg;
		
			//if ( $use_useraccount_for_mension ) {
				$error = commit_mention( $memb, $msg, $token,$token_secret,$use_dm);
			//}
			//else {
			//	$error = commit_mention( $memb, $msg );
			//}
		}
		if ( $error ) {
			return $error;
		}
	}
	return $error;
}

function write_json_result( $json_data ) {
	header("Content-type: application/json");
	echo json_encode($json_data);
	exit;
}

function get_latest_pastlog_no( $link ) {
	$sql = sprintf( "SELECT * FROM global" );
	$query = mysql_query( $sql, $link );
	while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
		$latest_pastlog = $row['latest_pastlog'];
	}
	return $latest_pastlog;
}

function rec_loginuser( $link, $screen_name )
{
	$sql = 'SELECT * FROM visitors ORDER BY timestamp DESC LIMIT 1';
	$query = mysql_query( $sql, $link );
	$last_login = '';
	if ( $query ) {
		while ( $row = @mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$last_login = $row['user'];
		}
	}
	if ( $screen_name != $last_login ) {
		$sql = sprintf( "INSERT INTO visitors (user, timestamp) VALUES ('%s', NOW())", $screen_name );
		$query = mysql_query( $sql, $link );
	}
}
