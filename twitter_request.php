<?php
require_once 'globals.php';
require_once 'twitteroauth.php';

//TwitterOAuthオブジェクトを作成
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

//リクエストトークンを取得
$request_token = $connection->getRequestToken();

session_start();
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

switch ($connection->http_code) {
  case 200:
    //twitterの認証ページへのURLを作成
    $url = $connection->getAuthorizeURL($token);
    header('Location: ' . $url);
    break;
  default:
    //twitterに接続できなかった場合
    echo 'Could not connect to Twitter. Refresh the page or try again later.';
}
