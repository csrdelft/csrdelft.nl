<?php
require_once 'configuratie.include.php';

require_once 'courant/courant.class.php';
require_once 'courant/courantcontent.class.php';
$courant = new Courant();

//niet verzenden bij geen rechten, en niet bij een lege courant.
if (!$courant->magVerzenden()) {
	invokeRefresh(CSR_ROOT . '/actueel/courant/', 'U heeft geen rechten om de courant te verzenden.');
	exit;
} elseif ($courant->getBerichtenCount() < 1) {
	invokeRefresh(CSR_ROOT . '/actueel/courant/', 'Lege courant kan niet worden verzonden');
	exit;
}

$mail = new CourantContent($courant);

if (isset($_GET['iedereen'])) {
	$mail->zend('csrmail@lists.knorrie.org');
	$courant->leegCache();
} else {
	$mail->zend('pubcie@csrdelft.nl');
}
?><a href="verzenden.php?iedereen=true"> aan iedereen verzenden</a>
