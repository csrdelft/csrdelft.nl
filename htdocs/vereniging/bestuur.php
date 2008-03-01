<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('include.config.php');

# de pagina-inhoud;
require_once('class.bestuur.php');
require_once('class.bestuurcontent.php');

$bestuur= new Bestuur();
if(isset($_GET['bestuur']) AND $_GET['bestuur']==(int)$_GET['bestuur']){
	$bestuur->loadBestuur((int)$_GET['bestuur']);
}

$body = new BestuurContent($bestuur);
if(isset($_GET['action']) AND $_GET['action']=='bewerken'){
	$body->setAction('edit');
	
	if(isset($_POST['verhaal'])){
		//opslaan
		$bestuur->setVerhaal($_POST['verhaal']);
		if($bestuur->save()){
			$body->invokeRefresh('Opslaan gelukt', CSR_ROOT.'vereniging/bestuur/'.$bestuur->getJaar());
		}else{
			echo 'de barig'; exit;
		}
	}
}

# zijkolom in elkaar jetzen
$zijkolom=new kolom();
$zijkolom->addObject(new BestuurZijkolomContent($bestuur));

# pagina weergeven
require_once('class.csrdelft.php');
$pagina=new csrdelft($body);

$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
