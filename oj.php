<?php

function modecheck {
	%in = $cgi->parse_form_data;
	if (exists($in{'mode'})) {
		$mode=$in{'mode'};
	}
	$phase = $session{'phase'};
	if ($phase eq "") {$phase = 'kekka';}
}

load_session_table();
modecheck();

if ($mode eq "start") {&mode_start;}
elsif ($mode eq "join") {&mode_join;}
elsif ($mode eq "repaircookie") {&mode_repaircookie;}
elsif ($mode eq "joincancel") {&mode_joincancel;}
elsif ($mode eq "change") {&mode_change;}
elsif ($mode eq "answer") {&mode_answer;}
elsif ($mode eq "giveup") {&mode_giveup;}
elsif ($mode eq "vote") {&mode_vote;}
elsif ($mode eq "addword") {&mode_addword;}
elsif ($mode eq "pastlog") {&mode_pastlog;}
elsif ($phase eq "sanka") {&phase_sanka;}
elsif ($phase eq "toukou") {&phase_toukou;}
elsif ($phase eq "kekka") {&phase_kekka;}
else {&phase_kekka;}

