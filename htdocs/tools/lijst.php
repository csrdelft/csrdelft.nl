<?php

require_once 'configuratie.include.php';
require_once 'lid/ledenlijstcontent.class.php';

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	# geen rechten
	require_once 'model/CmsPaginaModel.class.php';
	require_once 'view/CmsPaginaView.class.php';
	$body = new CmsPaginaView(CmsPaginaModel::get('403'));
	$pagina = new CsrLayoutPage($body);
	$pagina->view();
	exit;
}

$message = '';

$zoeker = new LidZoeker();

if (isset($_GET['q'])) {

	$query = $_GET;
	$zoeker->parseQuery($query);

	if ($zoeker->count() == 0) {
		// als er geen resultaten zijn dan verbreden we het statusfilter
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

if (isset($_GET['addToGoogleContacts'])) {
	try {
		require_once 'google/model/GoogleSyncModel.class.php';
		GoogleSyncModel::doRequestToken(CSR_ROOT . REQUEST_URI);

		$gSync = GoogleSyncModel::instance();

		$start = microtime(true);
		$message = $gSync->syncLidBatch($zoeker->getLeden());
		$elapsed = microtime(true) - $start;

		setMelding(
				'<h3>Google-sync-resultaat:</h3>' . $message . '<br />' .
				'<a href="/ledenlijst?q=' . htmlspecialchars($_GET['q']) . '">Terug naar de ledenlijst...</a>', 'Google-sync resultaat'
				, 0);

		if (LoginModel::mag('P_ADMIN')) {
			setMelding('Tijd nodig voor deze sync: ' . $elapsed . 's', 0);
		}
	} catch (Exception $e) {
		$m = $e->getMessage();
		$title = substr($m, strpos($m, '<title>') + 7, strpos($m, '</title>'));
		setMelding($title, -1);
	}
} else {

	//redirect to profile if only one result.
	if ($zoeker->count() == 1) {
		$leden = $zoeker->getLeden();
		$profiel = $leden[0];
		redirect('/profiel/' . $profiel->uid);
	}
}

if ($message != '') {
	setMelding($message, 0);
}

$pagina = new CsrLayoutPage($ledenlijstcontent);
$pagina->addCompressedResources('ledenlijst');
$pagina->view();
