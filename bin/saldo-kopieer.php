#!/usr/bin/php
<?php 
require_once('include.config.php');

$saldiQuery="SELECT uid, soccieID, saldo, createTerm, maalSaldo FROM socciesaldi;";

$result=$db->query($saldiQuery);

while($data=$db->next($result)){
	$updateLid=
		"UPDATE lid 
		SET
			soccieID=".$data['soccieID'].",
			createTerm='".$data['createTerm']."',
			soccieSaldo=".$data['saldo'].",
			maalcieSaldo=".$data['maalSaldo']."
		WHERE uid='".$data['uid']."'
		LIMIT 1;";echo $updateLid;
	$db->query($updateLid);	 
	
}



?>
