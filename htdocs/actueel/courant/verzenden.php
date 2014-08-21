<?php
require_once 'configuratie.include.php';

require_once 'courant/courant.class.php';
require_once 'courant/courantcontent.class.php';
$courant = new Courant();

//niet verzenden bij geen rechten, en niet bij een lege courant.
if (!$courant->magVerzenden()) {
	SimpleHTML::setMelding('U heeft geen rechten om de courant te verzenden.', -1);
	redirect(CSR_ROOT . '/actueel/courant/');
	exit;
} elseif ($courant->getBerichtenCount() < 1) {
	SimpleHTML::setMelding('Lege courant kan niet worden verzonden', 0);
	redirect(CSR_ROOT . '/actueel/courant/');
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
