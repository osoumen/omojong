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
    consumer_key    => 'oVHQOYjXkfrEOGEVdRosQ',
    consumer_secret => '5Z0zGHDWqBshT1nWa3wcCB7fx69kH7cNExPPdHAGR8',
);

# http://dev.twitter.com/apps/XXXXXX/my_token で取得できるやつ
my $ACCESS_TOKEN        = '207520259-a5z3WtxYG807hJGT1Ulat1GUcqolTX2dUPF0oVZT';
my $ACCESS_TOKEN_SECRET = '8c8P03bhKbOzwSnPYAxYBJZ6Hm9dscZ4Vwrffl356Pg';

# constructs a "Net::Twitter::Lite" object
my $t = Net::Twitter::Lite->new(%CONSUMER_TOKENS);

# トークンをセットする
$t->access_token($ACCESS_TOKEN);
$t->access_token_secret($ACCESS_TOKEN_SECRET);

# 投稿
my $status = $t->update({ status => 'Perlで投稿テスト' });

# 投稿した結果をダンプ
print Dumper $status;

