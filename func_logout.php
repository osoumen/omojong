<?php

require_once 'globals.php';

unset( $_SESSION['access_token'] );
setcookie( $gameid_param_name, '', time() - 3600 );

if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
	$auth_back_url = $_SERVER['HTTP_REFERER'];
	header('Location: ' . $auth_back_url);
}
else {
	header('Location: ' . $g_scripturl);
}