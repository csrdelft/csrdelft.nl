<?php

# prevent global namespace poisoning
main();
exit;
function main() {

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	session_start();
	$db = new MySQL();
	$lid = new Lid($db);

	# als er genoeg rechten zijn een preview van de csrmail laten zien.
	if ($lid->hasPermission('P_MAIL_COMPOSE')) {
		require_once('class.csrmail.php');
		$csrmail = new Csrmail($lid, $db);
		require_once('class.csrmailcomposecontent.php');
		$pagina = new Csrmailcomposecontent($csrmail, 'preview');
	} else {
		# geen rechten, geen voorbeeld
		require_once('class.includer.php');
		$pagina = new Includer('', 'geentoegang.html');
	}	
	$pagina->view();
}

?>