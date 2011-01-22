<?php

require_once 'globals.php';
require_once 'common.php';

//データベースに接続
$link = connect_db();

$session = load_session_table( $link );

//$mode = $_GET['mode'];
$phase = $session['phase'];
if ($phase == NULL) {
	$phase = 'kekka';
}

switch ( $phase ) {
	case 'sanka':
		include 'html_sanka.php';
		break;
		
	case 'toukou':
		include 'html_toukou.php';
		break;
		
	case 'kekka':
		include 'html_kekka.php';
		break;
		
	default:
		include 'html_kekka.php';
}

//データベースを切断
mysql_close( $link );
