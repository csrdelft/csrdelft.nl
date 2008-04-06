<?php
# C.S.R. Delft | vormingsbank@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# vormingsbank hulppagina, voor het beantwoorden van ajax zoekrequests
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
require_once('vb/class.vb.php');
require_once('vb/class.vbsearch.php');

$vb = new VB();
if ($vb->isLid())
{
	$search = new VBSearch($vb);
	$search->handleRequest();
}
else
	die("U bent geen lid");

?>
