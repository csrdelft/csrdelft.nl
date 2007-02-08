<?php

require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

//geen maalmod, dan terug naar de maaltijden...
if(!$lid->hasPermission('P_MAAL_MOD')){ header('location: http://csrdelft.nl/maaltijden/'); exit; }

$sStatus='';

if(is_array($_FILES) AND isset($_FILES['CSVSaldi'])){
	//bestandje uploaden en verwerken...
	$bCorrect=true;
	//niet met csv functies omdat dat misging met OS-X regeleinden...
	$aRegels=preg_split("/[\s]+/", file_get_contents($_FILES['CSVSaldi']['tmp_name']));

	$row=0;
	foreach($aRegels as $regel){
		$regel=str_replace(array('"', ' ', "\n", "\r"), '', $regel);
		$aRegel=explode(',', $regel);
		if($lid->isValidUid($aRegel[0]) AND is_numeric($aRegel[1])){
			$sQuery="UPDATE socciesaldi SET maalSaldo=".$aRegel[1]." WHERE uid='".$aRegel[0]."' LIMIT 1;";
			if(!$db->query($sQuery)){ $bCorrect=false; }
			$row++;
		}
	}

	if($bCorrect===true){
		$sStatus='Gelukt! er zijn '.$row.' regels ingevoerd; als dit er minder zijn dan u verwacht zitten er ongeldige regels in uw bestand.';
	}else{
		$sStatus='Helaas, er ging iets mis. Controleer uw bestand! mysql gaf terug <'.mysql_error().'>';
	}
}
class uploader{
	var $sStatus='';
	function uploader($sStatus){
		$this->sStatus=$sStatus;
	}
	function getTitel(){ return "MaalCie-saldi uploaden met een CSV-bestand"; }
	function view(){
		echo '<h1>MaalCie-saldi invoeren met een CSV-bestand.</h1>';
		if($this->sStatus!=''){
			echo '<div class="waarschuwing">'.$this->sStatus.'</div><br />';
		}
		?>
			<form name="CSVForm" action="saldi.php" method="post" enctype="multipart/form-data">
				<label for="CSVSaldi">CSV-bestand uploaden</label> 
				<input type="file" name="CSVSaldi" id="CSVSaldi" size="64" /><br />
				<input type="submit" name="submit" value="uploaden" />
			</form>
		<?php	
	}
}
$midden=new uploader($sStatus);

$zijkolom=new kolom();

$page=new csrdelft($midden, $lid, $db);
$page->setZijkolom($zijkolom);
$page->view();

?>
