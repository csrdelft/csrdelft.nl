<?php


namespace CsrDelft\controller;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\service\GoogleSync;
use CsrDelft\service\LidZoeker;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\lid\LedenlijstContent;

class LedenLijstController extends AbstractController {
	public function lijst(CmsPaginaRepository $cmsPaginaRepository, LidZoeker $lidZoeker) {
		if (!LoginModel::mag(P_OUDLEDEN_READ)) {
			# geen rechten
			$body = new CmsPaginaView($cmsPaginaRepository->find('403'));
			return view('default', ['content' => $body]);
		}

		$message = '';

		if (isset($_GET['q'])) {

			$query = $_GET;
			$lidZoeker->parseQuery($query);

			if ($lidZoeker->count() == 0) {
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
				$lidZoeker->parseQuery($query);
			}
		}

		$ledenlijstcontent = new LedenlijstContent($lidZoeker);

		if (isset($_GET['addToGoogleContacts'])) {
			try {
				GoogleSync::doRequestToken(CSR_ROOT . REQUEST_URI);

				$gSync = ContainerFacade::getContainer()->get(GoogleSync::class);

				$start = microtime(true);
				$message = $gSync->syncLidBatch($lidZoeker->getLeden());
				$elapsed = microtime(true) - $start;

				setMelding(
					'<h3>Google-sync-resultaat:</h3>' . $message . '<br />' .
					'<a href="/ledenlijst?q=' . htmlspecialchars($_GET['q'] ?? '') . '">Terug naar de ledenlijst...</a>', 0);

				if (LoginModel::mag(P_ADMIN)) {
					setMelding('Tijd nodig voor deze sync: ' . $elapsed . 's', 0);
				}
			} catch (CsrGebruikerException $e) {
				setMelding($e->getMessage(), -1);
			}
		} else {

			//redirect to profile if only one result.
			if ($lidZoeker->count() == 1) {
				$leden = $lidZoeker->getLeden();
				$profiel = $leden[0];
				return $this->redirectToRoute('profiel-profiel', ['uid' => $profiel->uid]);
			}
		}

		if ($message != '') {
			setMelding($message, 0);
		}

		return view('default', ['content' => $ledenlijstcontent]);
	}
}
