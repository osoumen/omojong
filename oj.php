<?php

require_once 'globals.php';
require_once 'common.php';

load_session_table();

$mode = $_GET['mode'];
$phase = $session['phase'];
if ($phase eq "") {
	$phase = 'kekka';
}
/*
if ($mode == "start") {&mode_start;}
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
