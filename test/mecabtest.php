<?php

require_once("twitteroauth.php");

// Consumer keyの値
$consumer_key = "oVHQOYjXkfrEOGEVdRosQ";
// Consumer secretの値
$consumer_secret = "5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8";
// Access Tokenの値
$access_token = "207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT";
// Access Token Secretの値
$access_token_secret = "8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg";

dl('mecab.so');

$mecab = new MeCab_Tagger();

// OAuthオブジェクト生成
$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);

//発言取得
$req = $to->OAuthRequest("https://twitter.com/statuses/user_timeline.xml","GET", array('id' => $_POST['id'], 'count' => $_POST['n']));
$xml = simplexml_load_string($req);

$stored_words = array();

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
?>
<html>
<head>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<title>mecab test</title>
</head>
<body>
<?php
foreach($stored_words as $word) {
	echo $word.'<br>';
}
?>
</body>
</html>