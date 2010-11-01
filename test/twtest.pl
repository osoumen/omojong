#!/usr/local/bin/perl

use strict;
use warnings;
use utf8;

use Net::Twitter::Lite;
use Data::Dumper;

=pod
twitterでOAuth認証を使ってつぶやくだけのサンプル
事前に http://dev.twitter.com/ で app として登録しておくこと
=cut

# http://dev.twitter.com/apps/XXXXXX で取得できるやつ
my %CONSUMER_TOKENS = (
    consumer_key    => 'QIxTtAj5K2vCdS89bIbyBw',
    consumer_secret => 'zvyPtmedWbhAK0wyGOTDGrSwa4pyGB9lEuwEcNdY',
);

# http://dev.twitter.com/apps/XXXXXX/my_token で取得できるやつ
my $ACCESS_TOKEN        = '200574591-jTqAKeJra9USKCIJ22zF8e1HSsuWlVOhxkgGO3T8';
my $ACCESS_TOKEN_SECRET = 'XMP7wZZHkbVdhQdj3q5YpDm6RPmROXtUupS6nVXszk';

# constructs a "Net::Twitter::Lite" object
my $t = Net::Twitter::Lite->new(%CONSUMER_TOKENS);

# トークンをセットする
$t->access_token($ACCESS_TOKEN);
$t->access_token_secret($ACCESS_TOKEN_SECRET);

# 投稿
my $status = $t->update({ status => 'Perlで投稿テスト' });

# 投稿した結果をダンプ
print Dumper $status;

