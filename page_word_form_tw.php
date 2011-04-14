<?php

require_once 'globals.php';
require_once 'common.php';
require_once 'twitteroauth.php';

dl('mecab.so');

//データベースに接続
$link = connect_db();

//いきなりこのページを開いたらtopへ
$session = load_session_table( $link );
if ( empty( $session ) ) {
	header('Location: ' . $g_scripturl);
}

//ログインしてなかったらtopに飛ぶ
session_start();
if ( is_login() == false ) {
	header('Location: ' . $g_scripturl);
}

//形態素解析エンジンの初期化
$mecab = new MeCab_Tagger();

// OAuthオブジェクト生成
$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,
$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);

//発言取得
//ログインされているはずなので、screen_nameを指定しなくても自分の発言が取得される
$tw_count = 100;
$since = date('r', time() - (75 * 24 * 60 * 60));
$req = $to->OAuthRequest("https://twitter.com/statuses/user_timeline.xml","GET", array('since' => $since, 'count' => $tw_count));
$xml = simplexml_load_string($req);

$stored_words = array();

//取得発言を解析し、単語抽出
foreach ($xml->status as $status) {
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
					array_push( $stored_words, $continued_word );
					$continued_word = '';
				}
			}
			$continued_word = $continued_word . $surface;
		}
		elseif ( $feature[0] == '助詞' || $feature[0] == '助動詞' )
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
					array_push( $stored_words, $continued_word );
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
	$sql = sprintf( "SELECT word FROM %s WHERE word = '%s'", $words_table_name, $newword );
	$query = mysql_query( $sql, $link );
	$found = mysql_num_rows( $query );
	//既にある単語は追加しない
	if ( $found == 0 ) {
		//単語をデータベースに書き込む
		$sql = sprintf( "INSERT INTO %s (word, date) VALUES ('%s', NOW())", $words_table_name, $newword );
		$query = mysql_query( $sql, $link );
		if ( $query ) {
			$totalwords++;
		}
	}
}

$smarty->assign( 'totalwords', $totalwords );
$smarty->display( $g_tpl_path . 'page_word_from_tw.tpl' );

//データベースを切断
mysql_close( $link );
