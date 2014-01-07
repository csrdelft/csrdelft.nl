<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------

try {
	# instellingen & rommeltjes
	require_once 'configuratie.include.php';

	if($loginlid->hasPermission('P_LOGGED_IN')) {
		header("Location: ". CSR_SERVER . "/leden");
	}

	# Tekst
	require_once 'pagina.class.php';
	require_once 'paginacontent.class.php';
	$thuis=new Pagina('thuis');
	$paginacontent = new PaginaContent($thuis);
	$paginacontent->setActie('bekijken');

	## pagina weergeven
	$pagina=new csrdelft($paginacontent, 'csrdelft2');
	$pagina->view('index');
}
catch (\Exception $e) { // TODO: logging
	
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}
