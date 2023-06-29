#!/usr/bin/perl

$ENV{'QUERY_STRING'} =~ s/\n//g;
$ENV{'QUERY_STRING'} =~ s/\s//g;

my $user = (split( /\&/, $ENV{'QUERY_STRING'}, 2 ))[0];
if ( !$user ) {
    print "Content-type: text/html\r\n\r\n";
    #print "Parâmetro inválido!\n";
    exit 1;
}

my $homedir = ( getpwnam($user) )[7];

if ( !$homedir || !-d $homedir || $homedir eq '/' ) {
    print "Content-type: text/html\r\n\r\n";
    #print "Usuário inválido!\n";
    exit 1;
}

if (!-e $homedir . '/public_html/forcedbackup/cpmove-' . $user . '.tar.gz') {
    print "Content-type: text/html\r\n\r\n";
    #print "Não existe nenhum arquivo de backup válido!\n";
exit 1;
}

my $acct_pkg;
my $acct_pkg = $homedir . '/public_html/forcedbackup/cpmove-' . $user . '.tar.gz';

if ( open my $pkg_fh, '<', $acct_pkg ){
    print "Content-type: application/x-tar\r\nContent-Encoding: x-gzip\r\n\r\n";
    while ( readline $pkg_fh ) {
        print;
    }
    close $pkg_fh;
    exit;
}
else {
    print "Content-type: text/html\r\n\r\n";
    #print "Não foi possível abrir o arquivo $!\n";
}

exit 1;