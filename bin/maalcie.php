#!/usr/bin/php
<?php 
error_reporting(E_ALL);


require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
require_once('include.common.php');
require_once('class.mysql.php');
require_once('class.lid.php');
session_start();
$db = new MySQL();
$lid = new Lid($db);


$row = 1;
$fp = fopen ("maalcie.csv","r");
while ($data = fgetcsv ($fp, 1000, ",")) {
	$sQuery="UPDATE socciesaldi SET maalSaldo=".$data[1]." WHERE uid='".$data[0]."' LIMIT 1;";
	$db->query( $sQuery);
  $row++;
}
fclose ($fp);


?>
