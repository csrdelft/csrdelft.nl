<?php

require_once('include.config.php');

//geen maalmod, dan terug naar de maaltijden...
if(!$lid->hasPermission('P_MAAL_MOD')){ header('location: http://csrdelft.nl/actueel/maaltijden/'); exit; }

$sStatus='';

require_once('class.saldi.php');
$sStatus=Saldi::putMaalcieCsv();

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
