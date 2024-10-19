<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\TextUtil;
use CsrDelft\service\GoogleContactSync;
use CsrDelft\service\LidZoekerService;
use CsrDelft\view\lid\LedenlijstContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LedenLijstController extends AbstractController
{
	/**
	 * @param Request $request
	 * @param LidZoekerService $lidZoeker
	 * @param GoogleContactSync $googleSync
	 * @param Environment $twig
	 * @return RedirectResponse|Response
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @Auth(P_OUDLEDEN_READ)
	 */
	#[Route(path: '/ledenlijst', methods: ['GET', 'POST'])]
	public function lijst(
		Request $request,
		LidZoekerService $lidZoeker,
		GoogleContactSync $googleSync,
		Environment $twig
	) {
		$message = '';

		if (isset($_GET['q'])) {
			$query = $_GET;
			$lidZoeker->parseQuery($query);

			if ($lidZoeker->count() == 0) {
				// als er geen resultaten zijn dan verbreden we het statusfilter
				if (isset($query['status'])) {
					if ($query['status'] == 'LEDEN') {
						$query['status'] = 'LEDEN|OUDLEDEN';
						$message =
							'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in **leden & oudleden**. Om alle leden en novieten weer te geven zoek met \'%\'.';
					} elseif ($query['status'] == 'LEDEN|OUDLEDEN') {
						$query['status'] = 'ALL';
						$message =
							'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in **alle leden**. Om alle leden en novieten weer te geven zoek met \'%\'.';
					}
				} else {
					$query['status'] = 'LEDEN|OUDLEDEN';
					$message =
						'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in **leden &amp; oudleden**. Om alle leden en novieten weer te geven zoek met \'%\'.';
				}
				$lidZoeker->parseQuery($query);
			}
		}

		if (isset($_GET['addToGoogleContacts'])) {
			try {
				$googleSync->initialize($request->getUri());
				$msg = $googleSync->syncLidBatch($lidZoeker->getLeden());
				$message = '<h3>Google-sync-resultaat:</h3>' . $msg;
			} catch (CsrGebruikerException $e) {
				$this->addFlash(FlashType::ERROR, $e->getMessage());
			}
		} elseif (isset($_GET['exportVcf'])) {
			$responseBody = '';

			foreach ($lidZoeker->getLeden() as $profiel) {
				$responseBody .= $twig->render('profiel/vcard.ical.twig', [
					'profiel' => $profiel,
				]);
			}

			$response = new Response(TextUtil::crlf_endings($responseBody), 200, [
				'Content-Type' => 'text/x-vcard',
				'Content-Disposition' => 'attachment; filename="ledenlijst.vcf"',
			]);
			$response->setCharset('UTF-8');

			return $response;
		} elseif ($lidZoeker->count() == 1) {
			//redirect to profile if only one result.
			$leden = $lidZoeker->getLeden();
			$profiel = $leden[0];
			return $this->redirectToRoute('csrdelft_profiel_profiel', [
				'uid' => $profiel->uid,
			]);
		}

		if ($message !== '') {
			$this->addFlash(FlashType::INFO, $message);
		}

		return $this->render('default.html.twig', [
			'content' => new LedenlijstContent($request, $lidZoeker),
		]);
	}
}
