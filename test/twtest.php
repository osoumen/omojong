<?php
// twitteroauth.phpを読み込む。パスはあなたが置いた適切な場所に変更してください
require_once("twitteroauth.php");

// Consumer keyの値
$consumer_key = "oVHQOYjXkfrEOGEVdRosQ";
// Consumer secretの値
$consumer_secret = "5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8";
// Access Tokenの値
$access_token = "207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT";
// Access Token Secretの値
$access_token_secret = "8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg";

// OAuthオブジェクト生成
$to = new TwitterOAuth($consumer_key,$consumer_secret,$access_token,$access_token_secret);

// TwitterへPOSTする。パラメーターは配列に格納する
// in_reply_to_status_idを指定するのならば array("status"=>"@hogehoge reply","in_reply_to_status_id"=>"0000000000"); とする。
$req = $to->OAuthRequest("https://twitter.com/statuses/update.xml","POST",array("status"=>"OAuth経由のポストテスト"));
// TwitterへPOSTするときのパラメーターなど詳しい情報はTwitterのAPI仕様書を参照してください

header("Content-Type: application/xml");
echo $req;
?>
