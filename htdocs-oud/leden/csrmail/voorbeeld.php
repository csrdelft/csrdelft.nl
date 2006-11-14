<?php

	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


	# als er genoeg rechten zijn een preview van de csrmail laten zien.
	if ($lid->hasPermission('P_MAIL_COMPOSE')) {
		require_once('class.csrmail.php');
		$csrmail = new Csrmail($lid, $db);
		require_once('class.csrmailcontent.php');
		require_once('class.csrmailcomposecontent.php');
		$pagina = new Csrmailcomposecontent($csrmail);
	} else {
		# geen rechten, geen voorbeeld
		require_once('class.includer.php');
		$pagina = new Includer('', 'geentoegang.html');
	}	
	$pagina->view();

?>
