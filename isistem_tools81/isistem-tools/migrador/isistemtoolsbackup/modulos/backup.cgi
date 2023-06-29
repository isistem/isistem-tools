#!/usr/bin/perl

BEGIN {
unshift @INC, '/usr/local/cpanel';
}

$| = 1;

eval "use Digest::MD5::File;";
my $has_md5_file = $@ ? 0 : 1;

my $user;
my $pass;

print "Content-type: text/html\r\n\r\n";
exit if $> == 0;

$ENV{'REQUEST_METHOD'} =~ tr/a-z/A-Z/;
if ($ENV{'REQUEST_METHOD'} eq "GET") {

    use Cpanel::Encoder::URI ();
    $ENV{'QUERY_STRING'} =~ s/\n//g;
    $ENV{'QUERY_STRING'} =~ s/\s//g;

    $user = (split( /\&/, $ENV{'QUERY_STRING'},2 ))[0];
    $user = (split( /\=/, $user ))[1];

    $pass = (split( /\&/, $ENV{'QUERY_STRING'},2 ))[1];
    $pass = (split( /\=/, $pass ))[1];
    $pass = Cpanel::Encoder::URI::uri_decode_str($pass);
}
else {
    use CGI;
    my $query = new CGI();

    $user = $query->param('user');
    $pass = $query->param('pass');
} 

#print "Usuário: $user<br />";
#print "Senha: $pass<br />";

$SIG{'ALRM'} = sub {
    print "O backup ultrapassou o limite de tempo!\n";
    exit 1;
};

my $homedir = ( getpwuid($>) )[7];
# my $user    = ( getpwuid($>) )[0];

if ( !$homedir || !-d $homedir ) {
    print "Não foi possível obter informações sobre a conta!\n";
    exit 1;
}

alarm 5400;

system ("export REMOTE_PASSWORD='$pass'; /usr/local/cpanel/scripts/pkgacct $user /$homedir/public_html/forcedbackup");

sleep 1;

if (!-e $homedir . '/public_html/forcedbackup/cpmove-' . $user . '.tar.gz') {
print "Não foi possível criar o arquivo de backup da conta!\n";
exit 1;
}


my $archive = $homedir . '/public_html/forcedbackup/cpmove-' . $user . '.tar.gz';
my $arquivo = 'cpmove-' . $user . '.tar.gz';

print "Arquivo de backup compactado: $arquivo\n";
my $md5 = get_file_md5($archive);
if ( $md5 ) {
    print "MD5 do arquivo: $md5\n";
}
print "\n";
exit;

sub get_file_md5 {
    my $file = shift;
    return if !$file || !-e $file;
    if ( $has_md5_file ) {
        return Digest::MD5::File::file_md5_hex($file);
    }
    else {
        # Linux
        foreach my $md5sum ( qw( /bin/md5sum /usr/bin/md5sum /usr/local/bin/md5sum /usr/sbin/md5sum /usr/local/sbin/md5sum ) ) {
            if ( -x $md5sum ) {
                my $md5_hex = `$md5sum $file`;
                chomp $md5_hex;
                $md5_hex =~ m/^\s*(\S+)\s+/;
                return $1;
            }
        }
        # BSD
        foreach my $md5sum ( qw( /bin/md5 /usr/bin/md5 /usr/local/bin/md5 /usr/sbin/md5 /usr/local/sbin/md5 ) ) {
            if ( -x $md5sum ) {
                my $md5_hex = `$md5sum $file`;
                chomp $md5_hex;
                $md5_hex =~ m/[=]\s+(\S+)\s*$/;
                return $1;
            }
        }
    }
}