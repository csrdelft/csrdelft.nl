#!/usr/bin/php
<?php 
error_reporting(E_ALL);


require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


$row = 1;
$fp = fopen ("maalcie.csv","r");
while ($data = fgetcsv ($fp, 1000, ",")) {
	$sQuery="UPDATE socciesaldi SET maalSaldo=".$data[1]." WHERE uid='".$data[0]."' LIMIT 1;";
	$db->query( $sQuery);
  $row++;
}
fclose ($fp);


?>
