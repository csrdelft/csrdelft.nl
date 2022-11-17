<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\service\GoogleContactSync;
use CsrDelft\service\LidZoekerService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\lid\LedenlijstContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LedenLijstController extends AbstractController
{
	/**
	 * @param Request $request
	 * @param CmsPaginaRepository $cmsPaginaRepository
	 * @param LidZoekerService $lidZoeker
	 * @param GoogleContactSync $googleSync
	 * @param Environment $twig
	 * @return RedirectResponse|Response
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 * @Route("/ledenlijst", methods={"GET", "POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function lijst(
		Request $request,
		CmsPaginaRepository $cmsPaginaRepository,
		LidZoekerService $lidZoeker,
		GoogleContactSync $googleSync,
		Environment $twig
	) {
		if (!$this->mag(P_OUDLEDEN_READ)) {
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
						$message =
							'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>leden &amp; oudleden</em>.';
					} elseif ($query['status'] == 'LEDEN|OUDLEDEN') {
						$query['status'] = 'ALL';
						$message =
							'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>alle leden</em>.';
					}
				} else {
					$query['status'] = 'LEDEN|OUDLEDEN';
					$message =
						'Zoekterm gaf geen resultaten met gegeven statusfilter, gezocht in <em>leden &amp; oudleden</em>.';
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
				setMelding($e->getMessage(), -1);
			}
		} elseif (isset($_GET['exportVcf'])) {
			$responseBody = '';

			foreach ($lidZoeker->getLeden() as $profiel) {
				$responseBody .= $twig->render('profiel/vcard.ical.twig', [
					'profiel' => $profiel,
				]);
			}

			$response = new Response(crlf_endings($responseBody), 200, [
				'Content-Type' => 'text/x-vcard',
				'Content-Disposition' => 'attachment; filename="ledenlijst.vcf"',
			]);
			$response->setCharset('UTF-8');

			return $response;
		} else {
			//redirect to profile if only one result.
			if ($lidZoeker->count() == 1) {
				$leden = $lidZoeker->getLeden();
				$profiel = $leden[0];
				return $this->redirectToRoute('csrdelft_profiel_profiel', [
					'uid' => $profiel->uid,
				]);
			}
		}

		if ($message != '') {
			setMelding($message, 0);
		}

		return $this->render('default.html.twig', [
			'content' => new LedenlijstContent($request, $lidZoeker),
		]);
	}
}
