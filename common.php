<?php

function connect_db {
	my $dbh = DBI->connect("DBI:mysql:$g_database", $g_dbuser, $g_dbpassword)
	or &error("DB error : $DBI::errstr");
	$dbh->do("SET NAMES utf8") or &error("DB error : $DBI::errstr");
	return $dbh;
}

function is_exist_table {
	my ($dbh, $table_name) = @_;
	
	#テーブルの存在チェック
	my $result = $dbh->prepare("SHOW TABLES WHERE Tables_in_$g_database = '$table_name';")
	or &error("DB error : $DBI::errstr");
	$result->execute() or &error("DB error : $DBI::errstr");
	my $exists = $result->rows;
	$result->finish() or &error("DB error : $DBI::errstr");
	
	return $exists;
}

function load_session_table {
	my($username,$href);
	
	$session = array();
	$members = array();
	$stock = array();
	$changerest = array();
	
	#データベースに接続
	my $dbh = &connect_db();

	#セッション情報を読み込む
	my $result = $dbh->prepare("SELECT * FROM session;") or &error("DB error : $DBI::errstr");
	$result->execute() or &error("DB error : $DBI::errstr");
	my $href = $result->fetchrow_hashref();
	$session{'leadername'} = $href->{'leadername'};
	$session{'phase'} = $href->{'phase'};
	$session{'ninzuu'} = $href->{'ninzuu'};
	$session{'ninzuu_max'} = $href->{'ninzuu_max'};
	$session{'maisuu'} = $href->{'maisuu'};
	$session{'change_quant'} = $href->{'change_quant'};
	$session{'change_amount'} = $href->{'change_amount'};
	$result->finish() or &error("DB error : $DBI::errstr");
	
	#参加者情報を読み込む
	$result = $dbh->prepare("SELECT * FROM members;") or &error("DB error : $DBI::errstr");
	$result->execute() or &error("DB error : $DBI::errstr");
	while ( $href = $result->fetchrow_hashref() ) {
		$username = $href->{'username'};
		push(@members, $username);
		$stock{$username} = $href->{'stock'};
		$changerest{$username} = $href->{'changerest'};
		$change_amount{$username} = $href->{'change_amount'};
	}
	$result->finish() or &error("DB error : $DBI::errstr");

	#データベースを切断
	$dbh->disconnect();
}

function numlist2sentence( $numlist ) {
	if (!defined(@words)) { load_words_table(); }
	my @listwords;
	foreach (@numlist) {
		my $sent = $words[$_];
		push(@listwords,$sent);
	}
	return join(" ",@listwords);
}

function supply_stock {
	my(@wordnumber);
	&load_words_table;
	
	@wordnumber = &get_availablewordlist;
	
	#札を配る
	foreach (@members) {
		$stock{$_} = join(",",splice(@wordnumber,0,$session{'maisuu'}));
	}
}

#使用可能な単語のリストを得る
#要load_words_table
function get_availablewordlist {
	my(@usedlist,@wordnumber,@rnd,@href);
	
	#使われている札の番号の配列を得る
	foreach (@members) {
		push(@usedlist,split(/,/,$stock{$_}));
	}

	#データベースに接続
	my $dbh = &connect_db();

	#投稿されている中に使用された札リストを得る
	$result = $dbh->prepare("SELECT wordlist FROM kaitou;") or &error("DB error : $DBI::errstr");
	$result->execute() or &error("DB error : $DBI::errstr");
	while ( @href = $result->fetchrow_array() ) {
		push( @usedlist,split(/,/, $href[0]) );
	}
	$result->finish() or &error("DB error : $DBI::errstr");

	#データベースを切断
	$dbh->disconnect();
	
	#残っている札番号の配列を得る
	@wordnumber = 0..($totalwords-1);
	foreach (@usedlist) { $wordnumber[$_] = -1; }
	@wordnumber = grep($_ ne -1,@wordnumber);

	#シャッフルする
	while (@wordnumber) {
 	   push(@rnd, splice(@wordnumber , rand @wordnumber , 1));
	}
	return @rnd;
}

function commit_mention {
	local($mlad,$inmsg) = @_;
	my $err = 0;
	my $t = Net::Twitter::Lite->new(%g_consumer_tokens);
	
	# トークンをセットする
	$t->access_token($g_access_token);
	$t->access_token_secret($g_access_token_secret);
	
	# 投稿
	my $notify_msg = "\@$mlad $inmsg";
	my $utf8 = Encode::decode_utf8 $notify_msg;
	my $status = $t->update({ status => $utf8 });
	
	return $err;
}

function is_member {
	my $name = $_[0];
	if (grep($_ eq $name, @members)) {
		return 1;
	}
	return 0;
}

function store_session_table {
	#データベースに接続
	my $dbh = &connect_db();
	
	#セッション情報をクリアする
	my $result = $dbh->do("DELETE FROM session;") or &error("DB error : $DBI::errstr");
	
	#セッション情報を書き込む
	$result = $dbh->do("INSERT INTO session VALUES(
	'$session{'leadername'}',
	'',
	'$phase',
	$session{'ninzuu'},
	$session{'ninzuu_max'},
	$session{'maisuu'},
	$session{'change_quant'},
	$session{'change_amount'}
	);") or &error("DB error : $DBI::errstr");
	
	#参加者情報情報をクリアする
	my $result = $dbh->do("DELETE FROM members;") or &error("DB error : $DBI::errstr");
	
	#参加者情報を書き込む
	foreach (@members) {
		$result = $dbh->do("INSERT INTO members VALUES(
		'$_',
		'$stock{$_}',
		$changerest{$_},
		$change_amount{$_}
		);") or &error("DB error : $DBI::errstr");
	}
	
	#データベースを切断
	$dbh->disconnect();
}

function load_words_table {
	my(@href);
	if (!defined(@words)) {
		#データベースに接続
		my $dbh = &connect_db();
		
		#単語を読み込む
		$result = $dbh->prepare("SELECT word FROM words;") or &error("DB error : $DBI::errstr");
		$result->execute() or &error("DB error : $DBI::errstr");
		@words = ();
		while ( @href = $result->fetchrow_array() ) {
			push( @words, $href[0] );
		}
		$result->finish() or &error("DB error : $DBI::errstr");
		$totalwords=@words;
		
		#今日追加された単語数を取得する
		$result = $dbh->prepare("SELECT word FROM words WHERE TO_DAYS( NOW() ) = TO_DAYS( date );") or &error("DB error : $DBI::errstr");
		$result->execute() or &error("DB error : $DBI::errstr");
		$todaywords = $result->rows;
		$result->finish() or &error("DB error : $DBI::errstr");

		#昨日追加された単語数を取得する
		$result = $dbh->prepare("SELECT word FROM words WHERE TO_DAYS( NOW() ) - TO_DAYS( date ) = 1;") or &error("DB error : $DBI::errstr");
		$result->execute() or &error("DB error : $DBI::errstr");
		$yesterdaywords = $result->rows;
		$result->finish() or &error("DB error : $DBI::errstr");

		#データベースを切断
		$dbh->disconnect();
	}
}

function refresh_kaitou_table {
	my($numlogs,$oldnum);

	#データベースに接続
	my $dbh = &connect_db();
	
	if ( &is_exist_table( $dbh, "kaitou" ) ) {
		#過去ログのファイル名をひとつずつ送る
		for ($numlogs=0; &is_exist_table($dbh, "kaitou_$numlogs"); $numlogs++) {}
		for (; $numlogs>0; $numlogs--) {
			$oldnum = $numlogs-1;
			$dbh->do("ALTER TABLE kaitou_$oldnum RENAME TO kaitou_$numlogs;")
			or &error("DB error : $DBI::errstr");
		}
		$dbh->do("ALTER TABLE kaitou RENAME TO kaitou_0;")
		or &error("DB error : $DBI::errstr");
	}
	
	#データベースを切断
	$dbh->disconnect();
}
