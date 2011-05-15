<?php

require_once 'globals.php';
require_once 'common.php';


//ログインしてなかったら何も出力しない
if ( is_login() == false ) {
	exit;
}

// OAuthオブジェクト生成
$to = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,
$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);

// フォロワー一覧を取得する
$error = '';
$cursor = -1;
$data = array();

do {
	$req = $to->OAuthRequest("https://api.twitter.com/1/statuses/followers.json","GET",array("cursor"=>$cursor));
	$obj = json_decode($req);
	
	if ( isset( $obj->error ) ) {
		$error = $obj->error;
		exit;
	}
	
	$cursor = $obj->next_cursor;
	
	//print_r( $obj );
	
	foreach ( $obj->users as $users ) {
		$json = array();
		$json['value'] = $users->screen_name;
		$json['name'] = $users->name;
		$json['image'] = $users->profile_image_url;
		$data[] = $json;
	}
} while ( $cursor );

header("Content-type: application/json");
echo json_encode($data);