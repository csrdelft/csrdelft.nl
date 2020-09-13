<?php


namespace CsrDelft\controller;


use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\service\GoogleSync;
use CsrDelft\service\LidZoekerService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\lid\LedenlijstContent;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class LedenLijstController extends AbstractController {
	/**
	 * @param CmsPaginaRepository $cmsPaginaRepository
	 * @param LidZoekerService $lidZoeker
	 * @param GoogleSync $googleSync
	 * @return TemplateView|RedirectResponse
	 * @Route("/ledenlijst", methods={"GET", "POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function lijst(CmsPaginaRepository $cmsPaginaRepository, LidZoekerService $lidZoeker, GoogleSync $googleSync) {
		if (!LoginService::mag(P_OUDLEDEN_READ)) {
			# geen rechten
			$body = new CmsPaginaView($cmsPaginaRepository->find('403'));
			return $this->render('default.html.twig', ['content' => $body]);
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
				$googleSync->doRequestToken(CSR_ROOT . REQUEST_URI);

				$start = microtime(true);
				$message = $googleSync->syncLidBatch($lidZoeker->getLeden());
				$elapsed = microtime(true) - $start;

				setMelding(
					'<h3>Google-sync-resultaat:</h3>' . $message . '<br />' .
					'<a href="/ledenlijst?q=' . htmlspecialchars($_GET['q'] ?? '') . '">Terug naar de ledenlijst...</a>', 0);

				if (LoginService::mag(P_ADMIN)) {
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
				return $this->redirectToRoute('csrdelft_profiel_profiel', ['uid' => $profiel->uid]);
			}
		}

		if ($message != '') {
			setMelding($message, 0);
		}

		return $this->render('default.html.twig', ['content' => $ledenlijstcontent]);
	}
}
