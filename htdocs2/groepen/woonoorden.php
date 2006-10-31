<?php

# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# Het middenstuk
require_once('class.woonoord.php');
require_once('class.woonoordcontent.php');
$woonoord = new Woonoord($db, $lid);
if(isset($_GET['woonoordid'])){
  $iWoonoordID=(int)$_GET['woonoordid'];
  if(isset($_GET['verwijderen']) AND isset($_GET['uid']) AND preg_match('/^\w{4}$/', $_GET['uid']) AND 
  	($woonoord->magBewerken($iWoonoordID) OR $lid->hasPermission('P_LEDEN_MOD'))){
    //een bewoner verwijderen uit een woonoord
    $woonoord->delBewoner($iWoonoordID, $_GET['uid']);
    header('location: '.CSR_ROOT.'groepen/woonoorden.php');
    exit;
  }elseif( isset($_POST['rawBewoners']) AND $woonoord->magBewerken($iWoonoordID)){
  	$aBewoners=namen2uid($_POST['rawBewoners'], $lid);
      if(is_array($aBewoners) AND count($aBewoners)>0){
    	$iSuccesvol=0;
    	foreach($aBewoners as $aBewoner){
    		if(isset($aBewoner['uid'])){
    			$woonoord->addBewoner($iWoonoordID, $aBewoner['uid']);
    			$iSuccesvol++;
    		}
    	}
    	if($iSuccesvol==count($aBewoners)){
    		header('location: '.CSR_ROOT.'groepen/woonoorden.php#'.$iWoonoordID);
    		exit;
    	}
    }	
  }elseif(isset($_POST['bewoners']) AND is_array($_POST['bewoners']) AND $woonoord->magBewerken($iWoonoordID)){
    foreach($_POST['bewoners'] as $bewoner){
    	if(preg_match('/^\w{4}$/', $bewoner)){
    		$woonoord->addBewoner($iWoonoordID, $bewoner);
    	}
    }
    header('location: '.CSR_ROOT.'groepen/woonoorden.php');
    exit;
  }  
    
}
$midden = new WoonoordContent($woonoord, $lid);

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
