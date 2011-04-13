<?php
require_once 'globals.php';
require_once 'twitteroauth.php';

session_start();
//oauth_tokenが古くなっている場合は、topに戻る
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
	$_SESSION['oauth_status'] = 'oldtoken';
	header('Location: ' . $g_scripturl);
}

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

//アクセストークンを取得する
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

//許可済みトークンをSESSIONに保存する
$_SESSION['access_token'] = $access_token;

//未認証トークンをSESSIONから削除する
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

//HTTP response が 200 を返したら成功とみなし、メインページへリダイレクト
if (200 == $connection->http_code) {
	/* The user has been verified and the access tokens can be saved for future use */
	$_SESSION['status'] = 'verified';

//アクセストークンをデバッグ表示
//print_r($access_token);
	
	header('Location: ' . $g_scripturl);
}
else {
	//200以外を返した場合はアクセストークンの取得に失敗
	// Save HTTP status for error dialog on connnect page.
}
