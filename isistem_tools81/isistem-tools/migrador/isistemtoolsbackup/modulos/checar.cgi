#!/usr/bin/perl

print "Content-type: text/html\r\n\r\n";

$quser = $ENV{'QUERY_STRING'};
$quser =~ s/\n//g;
$quser =~ s/\r//g;

chomp( $pwd = `pwd` );
open( PASSWD, "/etc/passwd" );
while (<PASSWD>) {
    ( $name, $x, $uid, $gid, undef, $homedir, $shell ) = split( /:/, $_ );
    next if ( length($homedir) < 3 );

    if ( $pwd =~ /^${homedir}\// || $pwd =~ /^${homedir}$/ ) {
        $founduid = 1;
        last;
    }
}
close(PASSWD);
if ($founduid) {
    print "MYUID: $uid\n";
}
elsif ( getpwnam($quser) ) {
    $uid = ( getpwnam($quser) )[2];
    print "MYUID: $uid\n";
}

print "REALUID: $>\n";