<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/actueel/maaltijden/corveerooster.php
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'maaltijden/maaltrack.class.php';
require_once 'maaltijden/maaltijd.class.php';
$maaltrack = new MaalTrack();


require_once 'maaltijden/corveeroostercontent.class.php';
$beheer = new CorveeroosterContent($maaltrack);


$page=new csrdelft($beheer);
$page->setZijkolom(false);
$page->view();

?>
