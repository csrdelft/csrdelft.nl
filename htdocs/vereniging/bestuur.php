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

$body = new BestuurContent($bestuur, $lid);

# zijkolom in elkaar jetzen
$zijkolom=new kolom();
$zijkolom->addObject(new BestuurZijkolomContent($bestuur));

# pagina weergeven
require_once('class.csrdelft.php');
$pagina=new csrdelft($body, $lid, $db);

$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
