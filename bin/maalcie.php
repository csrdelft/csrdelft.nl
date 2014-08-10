#!/usr/bin/php
<?php 

require_once('/srv/www/www.csrdelft.nl/lib/configuratie.include.php');

$row = 1;
$fp = fopen ("maalcie.csv","r");
while ($data = fgetcsv ($fp, 1000, ",")) {
	$sQuery="UPDATE socciesaldi SET maalSaldo=".$data[1]." WHERE uid='".$data[0]."' LIMIT 1;";
	$db->query( $sQuery);
  $row++;
}
fclose ($fp);
echo $row.' regels ingevoerd;';
