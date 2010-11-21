<?php

require_once 'globals.php';
require_once 'common.php';

load_session_table();

$mode = $_GET['mode'];
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

/*
switch ( $mode ) {
	case 'start':
		include 'html_start.php';
		break;
		
	case 'join':
		include 'html_join.php';
		break;

	case 'repaircookie':
		include 'html_repaircookie.php';
		break;

	case 'joincancel':
		include 'html_joincancel.php';
		break;

	case 'change':
		include 'html_change.php';
		break;

	case 'answer':
		include 'html_answer.php';
		break;

	case 'giveup':
		include 'html_giveup.php';
		break;

	case 'vote':
		include 'html_vote.php';
		break;

	case 'addword':
		include 'html_addword.php';
		break;

	case 'pastlog':
		include 'html_pastlog.php';
		break;

	case 'vote':
		include 'html_vote.php';
		break;
		
	default:
}
*/
/*
if ($mode == "start") {&;}
elsif ($mode == "join") {&mode_join;}
elsif ($mode == "repaircookie") {&mode_repaircookie;}
elsif ($mode == "joincancel") {&mode_joincancel;}
elsif ($mode == "change") {&mode_change;}
elsif ($mode == "answer") {&mode_answer;}
elsif ($mode == "giveup") {&mode_giveup;}
elsif ($mode == "vote") {&mode_vote;}
elsif ($mode == "addword") {&mode_addword;}
elsif ($mode == "pastlog") {&mode_pastlog;}
elsif ($phase == "sanka") {&phase_sanka;}
elsif ($phase == "toukou") {&phase_toukou;}
elsif ($phase == "kekka") {&phase_kekka;}
else {&phase_kekka;}
*/
