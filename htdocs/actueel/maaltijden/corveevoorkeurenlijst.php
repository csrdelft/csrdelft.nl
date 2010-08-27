<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/corveevoorkeurenlijst
# -------------------------------------------------------------------
# Een eenvoudige lijst van de corveevoorkeuren van alle leden, die de 
# CorveeCaesar kan gebruiken bij het roostermaken. 
# -------------------------------------------------------------------

require_once 'include.config.php';

// Deze pagina is (voorlopig) alleen voor de maalcie bedoeld.
if(!$loginlid->hasPermission('P_MAAL_MOD')){ header('location: '.CSR_ROOT.'actueel/maaltijden/'); exit; }


$sorteer = 'uid';
$sorteer_richting = 'asc';
if (isset($_GET['sorteer'])) $sorteer = $_GET['sorteer'];
elseif (isset($_POST['sorteer'])) $sorteer = $_POST['sorteer'];
if (isset($_GET['sorteer_richting'])) $sorteer_richting = $_GET['sorteer_richting'];
elseif (isset($_POST['sorteer_richting'])) $sorteer_richting = $_POST['sorteer_richting'];

require_once 'maaltijden/class.corveevoorkeurencontent.php';
$lijst = new CorveevoorkeurenContent($sorteer, $sorteer_richting);

$page=new csrdelft($lijst);
$page->view();

?>
