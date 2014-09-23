<?php

require_once 'configuratie.include.php';
require_once 'lid/ledenlijstcontent.class.php';
require_once 'groepen/groep.class.php';

if (!(LoginModel::mag('P_LOGGED_IN') AND LoginModel::mag('P_OUDLEDEN_READ'))) {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	require_once 'MVC/view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
	$pagina = new CsrLayoutPage($body);
	$pagina->view();
	exit;
}

$message = '';

$zoeker = new LidZoeker();

if (isset($_GET['q'])) {

	$zoeker->parseQuery($_GET);

	//als er geen resultaten zijn dan kijken we of de query de naam is van een
	//h.t. groep. Als dat zo is refreshen we naar die groep.
	if ($zoeker->count() == 0) {
		try {
			$groep = new OldGroep($_GET['q']);
			if ($groep instanceof OldGroep) {
				redirect($groep->getUrl());
			}
		} catch (Exception $e) {
			//bestaat ie niet, dan doen we niets.
		}

		//als er ook geen h.t. groep is kijken we of er wel resultaat is bij
		//het verbreden van het statusfilter
		$query = $_GET;
		if (isset($query['status'])) {
			if ($query['status'] == 'LEDEN') {
				$query['status'] = 'LEDEN|OUDLEDEN';
				$message = 'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>leden &amp; oudleden</em>.';
			} elseif ($query['status'] == 'LEDEN|OUDLEDEN') {
				$query['status'] = 'ALL';
				$message = 'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>alle leden</em>.';
			}
		} else {
			$query['status'] = 'LEDEN|OUDLEDEN';
			$message = 'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>leden &amp; oudleden</em>.';
		}
		$zoeker->parseQuery($query);
	}
}

$ledenlijstcontent = new LedenlijstContent($zoeker);

if (isset($_GET['addToGoogle'])) {
	try {
		require_once 'googlesync.class.php';
		GoogleSync::doRequestToken(CSR_ROOT . REQUEST_URI);

		$gSync = GoogleSync::instance();

		$start = microtime();
		$message = $gSync->syncLidBatch($zoeker->getLeden());
		$elapsed = microtime() - $start;

		SimpleHTML::setMelding(
				'<h1>Google-sync-resultaat:</h1>' . $message . '<br />' .
				'<a href="/communicatie/lijst.php?q=' . htmlspecialchars($_GET['q']) . '">Terug naar de ledenlijst...</a>', 'Google-sync resultaat'
				, 0);

		if (LoginModel::mag('P_ADMIN')) {
			SimpleHTML::setMelding('<hr />Tijd nodig voor deze sync: ' . $elapsed . 'ms');
		}
	} catch (Zend_Gdata_App_AuthException $e) {
		$m = $e->getMessage();
		$title = substr($m, strpos($m, '<title>') + 7, strpos($m, '</title>'));
		SimpleHTML::setMelding($title, -1);
	}
} else {

	//redirect to profile if only one result.
	if ($zoeker->count() == 1) {
		$leden = $zoeker->getLeden();
		$lid = $leden[0];
		redirect(CSR_ROOT . '/communicatie/profiel/' . $lid->getUid());
	}
}

if ($message != '') {
	SimpleHTML::setMelding($message, 0);
}

$pagina = new CsrLayoutPage($ledenlijstcontent);

$pagina->addStylesheet('/layout/js/datatables/css/datatables_basic');
$pagina->addStylesheet('/layout/css/ledenlijst');
$pagina->addScript('/layout/js/datatables/jquery.dataTables');
$pagina->view();
