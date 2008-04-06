<?php
# C.S.R. Delft | vormingsbank@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# vormingsbank hoofdpagina
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
require_once('vb/class.vb.php');
require_once('vb/class.vbcontent.php');

$vb = new VB();

//default
$action ="home";
$id = "0"; 

if(isset($_POST["actie"])) 
	$action = $_POST["actie"];
else if(isset($_GET["actie"])) 
	$action = $_GET["actie"];
if(isset($_POST["id"]))
	$id = $_POST["id"];
else if(isset($_GET["id"]))
	$id = $_GET["id"];
$id = (int) $id;

# Het middenstuk
if ($vb->isLid()) {
	$midden = new VBContent($vb, $action,$id); //toon root thema's
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}

## zijkolom in elkaar jetzen
$zijkolom=new kolom();
$lastitems=new vbcontent($vb, 'lastposts',$id);
$zijkolom->add($lastitems);
	
$page=new csrdelft($midden);
$page->setZijkolom($zijkolom);
$page->addStylesheet('vb.css');
$page->view();
	
?>
