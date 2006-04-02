#!/usr/bin/php5
<?php

# CLI Maaltijdbeheer C.S.R. Delft
# (c) 2006 PubCie C.S.R. Delft 
# 20-01-2006 Hans van Kranenburg

	session_id('maaltrack-cli');

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	$db = new MySQL();
	$lid = new Lid($db);
	
	//moot
	$moot=1;
	
	//kringvolgende leden van een moot ophalen
	$sMootleden="SELECT uid FROM lid WHERE moot=".$moot." AND kring !=0";
	$rMootleden=$db->query($sMootleden);
	while($aMootledenData=$db->next($rMootleden)){
		$aMootleden[]=$aMootledenData['uid'];
	}
	
	//bestaande mootX abo's ophalen
	$sMootAbos="SELECT uid FROM maaltijdabo WHERE abosoort='A_MOOT".$moot."'";
	$rMootAbos=$db->query($sMootAbos);
	while($aMootAbosData=$db->next($rMootAbos)){
		$aMootAbos[]=$aMootAbosData['uid'];
	}
	
	//al bestaande mootX abo's van de lijst halen
	$aAboInvoeren=array_diff($aMootleden, $aMootAbos);
	
	//print_r($aAboInvoeren);
	
	//query's klussen
	foreach($aAboInvoeren as $sUid){
		$sQuery="INSERT INTO maaltijdabo (uid, abosoort )VALUES( '".$sUid."', 'A_MOOT".$moot."');";
		echo $sQuery;
		if(false){//$db->query($sQuery)){
			echo "   ...OK\r\n";
		}else{
			echo " ...shit\r\n";
		}
	}
	
	
?>