#!/usr/bin/perl

print "Content-type: text/html\r\n\r\n";
$ENV{'QUERY_STRING'} =~ s/\n//g;
$ENV{'QUERY_STRING'} =~ s/\s//g;

my $user = (split( /\&/, $ENV{'QUERY_STRING'}, 2 ))[0];
my $homedir = ( getpwnam($user) )[7];

my $acct_pkg = $homedir . '/public_html/forcedbackup/' . $user . '.tar.gz';
system '/bin/rm', '-rvf', $homedir . '/public_html/forcedbackup';
system '/bin/rm', '-rvf', $homedir . '/public_html/cgi-bin/forcedbackup';
print "FINALIZADO\n";