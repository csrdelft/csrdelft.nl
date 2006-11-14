<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# de pagina-inhoud;
require_once('class.bestuur.php');
require_once('class.bestuurcontent.php');
$bestuur= new Bestuur($lid, $db);
if(isset($_GET['bestuur']) AND $_GET['bestuur']==(int)$_GET['bestuur']){
	$bestuur->loadBestuur((int)$_GET['bestuur']);
}

$body = new BestuurContent($bestuur, $lid);

# zijkolom in elkaar jetzen
$zijkolom=new kolom();
$zijkolom->addObject(new BestuurZijkolomContent($bestuur, $lid));

# pagina weergeven
require_once('class.csrdelft.php');
$pagina=new csrdelft($body, $lid, $db);

$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
