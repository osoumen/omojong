#!/usr/local/bin/perl

my($sec,$min,$hour,$mday,$mon,$year,$wday) = gmtime(time);
@month=('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
@week = ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
$gmt = sprintf("%s, %02d-%s-%04d %02d:%02d:%02d GMT",
$week[$wday],$mday,$month[$mon],$year+1900,$hour,$min,$sec);
print "Set-Cookie: ojcgi=; expires=$gmt;\n";

print "Content-type: text/html\n\n";
print "<html><body>\n";

print "</body></html>\n";