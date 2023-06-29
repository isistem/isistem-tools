#!/usr/local/cpanel/3rdparty/bin/perl
#WHMADDON:isistem-tools:Isistem <b>Tools</b>
BEGIN {
   push(@INC,"/usr/local/cpanel/");
}
#use whmlib;
use SafeFile;
use Cpanel::Sys ();

print "Content-type: text/html\n\n";
print '<iframe src="isistem-tools/index.php" frameborder="0" width="100%" style="height: 100000px" scrolling="no" name="iframe_addon" target="_blank"></iframe>';
