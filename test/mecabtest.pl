#!/usr/local/bin/perl

use strict;
use MeCab;
use Net::Twitter::Lite;
use Encode;

my %CONSUMER_TOKENS = (
    consumer_key    => 'QIxTtAj5K2vCdS89bIbyBw',
    consumer_secret => 'zvyPtmedWbhAK0wyGOTDGrSwa4pyGB9lEuwEcNdY',
);

my $t = Net::Twitter::Lite->new(%CONSUMER_TOKENS);
my $statuses = $t->user_timeline({ id => 'Hiroki_Kikuta', count => 10 });

my $mecab = MeCab::Tagger->new();

my @stored_words = ();

eval {
	for my $status ( @$statuses ) {
		my $str = $status->{text};
		
		#mention,ハッシュタグを削除
		$str =~ s/\@\w+//g;
		$str =~ s/#\w+//g;
		$str =~ s/RT\W+//g;
		
		#括弧で囲まれた部分を削除
		$str =~ s/\(.*\)//g; 
		
		#URLを削除
		$str =~ s/http:\/\/\S*//g;
		
		#空白を削除
		$str =~ s/\s*//g;
		
#		$str = Encode::encode_utf8 ($str."\n\n");
#		print $str;
		
		#形態素解析
		my $node = $mecab->parseToNode($str);
		
		my $continued_word = '';
		my $prev_midasi = '';
		my $prev_hinsi = '';
		my $prev_hinsi_d1 = '';
		for( ; $node; $node = $node->{next} ) {
			next unless defined $node->{surface};
			my $midasi = $node->{surface};
			#品詞分類１まで使用
			my( $hinsi, $hinsi_d1 ) = (split( /,/, $node->{feature} ))[0,1];
#			print $midasi, "\t", $hinsi,",", $hinsi_d1, "\n";
			
			#記号は無視
			#感動詞
			#副詞
			#[接頭詞]+(名詞.*)+[助詞]
			#動詞+[(助動詞.*)]
			#形容詞+[助詞]
			if (
				$hinsi eq '感動詞' ||
				$hinsi eq '副詞' ||
				$hinsi eq '名詞' ||
				$hinsi eq '接頭詞' ||
				$hinsi eq '動詞' ||
				$hinsi eq '形容詞' ||
				$hinsi eq '接頭詞' ||
				$hinsi eq 'BOS/EOS'
				)
			{
				#-切る-
				#感動詞
				#副詞
				#接頭詞
				#名詞（nextが名詞でない）
				#動詞,(!非自立&!接尾)
				#形容詞,(!非自立&!接尾)
				#接頭詞
				if ( not (
					( $hinsi_d1 eq '非自立' || $hinsi_d1 eq '接尾' ) ||
					( $hinsi eq '名詞' && $prev_hinsi eq '名詞' ) ||
					( $hinsi eq '名詞' && $prev_hinsi eq '接頭詞' ) ) ||
					( $prev_hinsi_d1 eq '接尾' )	#直前が接尾なら切る
					)
				{
					if ( $continued_word )
					{
						push( @stored_words, $continued_word );
						$continued_word = '';
					}
				}
				$continued_word = $continued_word . $midasi;
			}
			elsif ( $hinsi eq '助詞' || $hinsi eq '助動詞' )
			{
				#-切らない-
				#助詞,助動詞
				#*,(非自立|接尾)
				#名詞（prevが名詞or接頭詞）
				$continued_word = $continued_word . $midasi;
			}
			else {
				#句読点で切る
				if ( ( $hinsi eq '記号' && $hinsi_d1 eq '句点' ) || ( $hinsi eq '記号' && $hinsi_d1 eq '読点' ) )
				{
					if ( $continued_word )
					{
						push( @stored_words, $continued_word );
						$continued_word = '';
					}
				}
				#-無視-
				#記号
				elsif ( $hinsi ne '記号' ) {
					$continued_word = $continued_word . $midasi;
				}
			}
			
			$prev_midasi = $midasi;
			$prev_hinsi = $hinsi;
			$prev_hinsi_d1 = $hinsi_d1;
		}
	}
};

foreach(@stored_words) {
	print $_."\n";
}

