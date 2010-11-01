#!/usr/local/bin/perl

use strict;
use Encode;
use DBI;

my $database = 'omojong';
my $user = 'ojbot';
my $password = 'korogottu';

my $dbh = DBI->connect("DBI:mysql:$database", $user, $password)
	or die "Can't connect : $DBI::errstr";

my $result = $dbh->do('create table session (
	leadername text,
	session_key text,
	phase text,
	ninzuu text,
	ninzuu_max text,
	maisuu text,
	change_quant text,
	change_amount text
	);');
die "Can't execute : $DBI::errstr" unless($result);
$dbh->disconnect();
