<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\commissievoorkeuren\CommissieVoorkeuren;
use CsrDelft\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\repository\corvee\CorveeKwalificatiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\repository\groepen\KetzersRepository;
use CsrDelft\repository\groepen\OnderverenigingenRepository;
use CsrDelft\repository\groepen\RechtenGroepenRepository;
use CsrDelft\repository\groepen\WerkgroepenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\fiscaat\SaldoGrafiekService;
use CsrDelft\service\GoogleSync;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenType;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\profiel\ExternProfielForm;
use CsrDelft\view\profiel\InschrijfLinkForm;
use CsrDelft\view\profiel\ProfielForm;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class ProfielController extends AbstractController {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var GoogleSync
	 */
	private $googleSync;

	public function __construct(
		ProfielRepository $profielRepository,
		AccountRepository $accountRepository,
		LidToestemmingRepository $lidToestemmingRepository,
		GoogleSync $googleSync
	) {
		$this->profielRepository = $profielRepository;
		$this->accountRepository = $accountRepository;
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->googleSync = $googleSync;
	}

	/**
	 * @param $uid
	 * @return RedirectResponse
	 * @Route("/profiel/{uid}/resetPrivateToken", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_PROFIEL_EDIT)
	 */
	public function resetPrivateToken($uid): RedirectResponse
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		if ($profiel->account == null) {
			throw new NotFoundHttpException("Profiel heeft geen account");
		}

		$this->accountRepository->resetPrivateToken($profiel->account);

		return $this->redirectToRoute('csrdelft_profiel_profiel', ['uid' => $uid]);
	}

	/**
	 * @param BesturenRepository $besturenRepository
	 * @param CommissiesRepository $commissiesRepository
	 * @param WerkgroepenRepository $werkgroepenRepository
	 * @param OnderverenigingenRepository $onderverenigingenRepository
	 * @param RechtenGroepenRepository $rechtenGroepenRepository
	 * @param KetzersRepository $ketzersRepository
	 * @param ActiviteitenRepository $activiteitenRepository
	 * @param CiviBestellingRepository $civiBestellingRepository
	 * @param CorveeTakenRepository $corveeTakenRepository
	 * @param CorveeVoorkeurenRepository $corveeVoorkeurenRepository
	 * @param BoekExemplaarRepository $boekExemplaarRepository
	 * @param BoekRecensieRepository $boekRecensieRepository
	 * @param FotoRepository $fotoRepository
	 * @param MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	 * @param CorveeVrijstellingenRepository $corveeVrijstellingenRepository
	 * @param ForumPostsRepository $forumPostsRepository
	 * @param FotoTagsRepository $fotoTagsRepository
	 * @param CorveeKwalificatiesRepository $corveeKwalificatiesRepository
	 * @param MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository
	 * @param Profiel|null $profiel
	 * @return Response
	 * @throws Throwable
	 * @Route("/profiel/{uid}", methods={"GET"}, defaults={"uid": null}, requirements={"uid": ".{4}"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function profiel(
		BesturenRepository $besturenRepository,
		CommissiesRepository $commissiesRepository,
		WerkgroepenRepository $werkgroepenRepository,
		OnderverenigingenRepository $onderverenigingenRepository,
		RechtenGroepenRepository $rechtenGroepenRepository,
		KetzersRepository $ketzersRepository,
		ActiviteitenRepository $activiteitenRepository,
		CiviBestellingRepository $civiBestellingRepository,
		CorveeTakenRepository $corveeTakenRepository,
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository,
		BoekExemplaarRepository $boekExemplaarRepository,
		BoekRecensieRepository $boekRecensieRepository,
		FotoRepository $fotoRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		CorveeVrijstellingenRepository $corveeVrijstellingenRepository,
		ForumPostsRepository $forumPostsRepository,
		FotoTagsRepository $fotoTagsRepository,
		CorveeKwalificatiesRepository $corveeKwalificatiesRepository,
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		Profiel $profiel = null
	): Response
	{
		if (!$profiel) {
			$profiel = $this->getProfiel();
		}
		$fotos = [];
		foreach ($fotoTagsRepository->findBy(['keyword' => $profiel->uid], null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = $fotoRepository->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}

		return $this->render('profiel/profiel.html.twig', [
			'profiel' => $profiel,
			'besturen' => $besturenRepository->getGroepenVoorLid($profiel),
			'commissies_ft' => $commissiesRepository->getGroepenVoorLid($profiel, GroepStatus::FT),
			'commissies_ht' => $commissiesRepository->getGroepenVoorLid($profiel, GroepStatus::HT),
			'commissies_ot' => $commissiesRepository->getGroepenVoorLid($profiel, GroepStatus::OT),
			'werkgroepen' => $werkgroepenRepository->getGroepenVoorLid($profiel),
			'onderverenigingen' => $onderverenigingenRepository->getGroepenVoorLid($profiel),
			'groepen' => $rechtenGroepenRepository->getGroepenVoorLid($profiel),
			'ketzers' => $ketzersRepository->getGroepenVoorLid($profiel),
			'activiteiten' => $activiteitenRepository->getGroepenVoorLid($profiel),
			'bestellinglog' => $civiBestellingRepository->getBestellingenVoorLid($profiel->uid, 10),
			'bestellingenlink' => '/fiscaat/bestellingen' . ($this->getUid() === $profiel->uid ? '' : '/' . $profiel->uid),
			'corveetaken' => $corveeTakenRepository->getTakenVoorLid($profiel),
			'corveevoorkeuren' => $corveeVoorkeurenRepository->getVoorkeurenVoorLid($profiel->uid),
			'corveevrijstelling' => $corveeVrijstellingenRepository->getVrijstelling($profiel->uid),
			'corveekwalificaties' => $corveeKwalificatiesRepository->getKwalificatiesVanLid($profiel->uid),
			'forumpostcount' => $forumPostsRepository->getAantalForumPostsVoorLid($profiel->uid),
			'forumrecent' => $forumPostsRepository->getRecenteForumPostsVanLid($profiel->uid, (int)lid_instelling('forum', 'draden_per_pagina')),
			'boeken' => $boekExemplaarRepository->getEigendom($profiel->uid),
			'recenteAanmeldingen' => $maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid($profiel->uid, date_create_immutable(instelling('maaltijden', 'recent_lidprofiel'))),
			'abos' => $maaltijdAbonnementenRepository->getAbonnementenVoorLid($profiel->uid),
			'gerecenseerdeboeken' => $boekRecensieRepository->getVoorLid($profiel->uid),
			'fotos' => $fotos
		]);
	}

	/**
	 * @param $lidjaar
	 * @param $status
	 * @param EntityManagerInterface $em
	 * @return RedirectResponse|Response
	 * @Route("/profiel/{lidjaar}/nieuw/{status}", methods={"GET", "POST"}, requirements={"uid": ".{4}"})
	 * @Auth({P_LEDEN_MOD,"commissie:NovCie"})
	 * @CsrfUnsafe()
	 */
	public function nieuw($lidjaar, $status, EntityManagerInterface $em) {
		if ($em->getFilters()->isEnabled('verbergNovieten')) {
			$em->getFilters()->disable('verbergNovieten');
		}
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) || !in_array($lidstatus, LidStatus::getEnumValues())) {
			throw $this->createAccessDeniedException();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet && !LoginService::mag(P_LEDEN_MOD)) {
			throw $this->createAccessDeniedException();
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = $this->profielRepository->nieuw((int)$lidjaar, $lidstatus);

		return $this->profielBewerken($profiel, true);
	}

	private function profielBewerken(Profiel $profiel, $alleenFormulier = false) {

		if (!$profiel->magBewerken()) {
			throw $this->createAccessDeniedException();
		}
		$form = new ProfielForm($profiel, $alleenFormulier);
		if ($form->validate()) {
			$diff = $form->diff();
			if (empty($diff)) {
				setMelding('Geen wijzigingen', 0);
			} else {
				$nieuw = $profiel->uid === null || $this->profielRepository->find($profiel->uid) == null;
				$changeEntry = ProfielRepository::changelog($diff, $this->getUid());
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						array_push($changeEntry->entries, ...$this->profielRepository->wijzig_lidstatus($profiel, $change->old_value));
					}
				}
				$profiel->changelog[] = $changeEntry;
				if ($nieuw) {
					try {
						/** @var Connection $conn */
						$conn = $this->getDoctrine()->getConnection();
						$conn->setAutoCommit(false);
						$conn->connect();
						try {
							$this->profielRepository->create($profiel);

							if (filter_input(INPUT_POST, 'toestemming_geven') === 'true') {
								// Sla toesteming op.
								$toestemmingForm = new ToestemmingModalForm($this->lidToestemmingRepository, true);
								if ($toestemmingForm->validate()) {
									$this->lidToestemmingRepository->saveForLid($profiel->uid);
								} else {
									throw new CsrException('Opslaan van toestemming mislukt');
								}
							}
							$conn->commit();
						} catch (Exception $e) {
							setMelding($e->getMessage(), -1);
							$conn->rollBack();
						} finally {
							$conn->setAutoCommit(true);
						}
					} catch (CsrException $ex) {
						setMelding($ex->getMessage(), -1);
					}

					setMelding('Profiel succesvol opgeslagen met lidnummer: ' . $profiel->uid, 1);
				} elseif (1 === $this->profielRepository->update($profiel)) {
					setMelding(count($diff) . ' wijziging(en) succesvol opgeslagen', 1);
				} else {
					setMelding('Opslaan van ' . count($diff) . ' wijziging(en) mislukt', -1);
				}
			}
			return $this->redirectToRoute('csrdelft_profiel_profiel', ['uid' => $profiel->uid]);
		}
		if ($alleenFormulier) {
			return $this->render('plain.html.twig', ['titel' => 'Noviet toevoegen', 'content' => $form]);
		}
		return $this->render('default.html.twig', ['content' => $form]);
	}

	/**
	 * @param $uid
	 * @return RedirectResponse|Response
	 * @Route("/profiel/{uid}/bewerken", methods={"GET", "POST"}, requirements={"uid": ".{4}"})
	 * @Auth(P_PROFIEL_EDIT)
	 */
	public function bewerken($uid) {
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		return $this->profielBewerken($profiel);
	}

	/**
	 * @Route("/inschrijflink", methods={"GET", "POST"}, name="inschrijflink")
	 * @Auth({P_LEDEN_MOD,"commissie:NovCie"})
	 * @return Response
	 */
	public function externInschrijfLink(): Response
	{
		$form = new InschrijfLinkForm();
		$link = null;
		if ($form->validate()) {
			$values = $form->getValues();
			$string = implode(';', [
				$values['voornaam'],
				$values['tussenvoegsel'],
				$values['achternaam'],
				$values['email'],
				$values['mobiel']
			]);
			$token = base64url_encode($string);
			$link = $this->generateUrl('extern-inschrijven', ['pre' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
			$_POST = [];
			$form = new InschrijfLinkForm();
		}

		return $this->render('extern-inschrijven/link.html.twig', [
			'link' => $link,
			'form' => $form
		]);
	}

	/**
	 * @Route("/inschrijven/{pre}", methods={"GET", "POST"}, name="extern-inschrijven")
	 * @Auth(P_PUBLIC)
	 * @CsrfUnsafe()
	 * @param string $pre
	 * @param EntityManagerInterface $em
	 * @return Response
	 * @throws ConnectionException
	 */
	public function externInschrijfformulier(string $pre, EntityManagerInterface $em): Response
	{
		if (isDatumVoorbij('2021-08-28 00:00:00')) {
			return $this->render('extern-inschrijven/tekstpagina.html.twig', [
				'titel' => 'C.S.R. Delft - Inschrijven',
				'content' => '
				<h1 class="Titel">Inschrijvingen gesloten</h1>
				<p>Neem contact op met <a href="mailto:novcie@csrdelft.nl">novcie@csrdelft.nl</a></p>
			'
			]);
		}

		if ($em->getFilters()->isEnabled('verbergNovieten')) {
			$em->getFilters()->disable('verbergNovieten');
		}
		$profiel = $this->profielRepository->nieuw(date_create_immutable()->format('Y'), LidStatus::Noviet);

		if (empty($pre)) {
			throw new NotFoundHttpException();
		}
		$data = base64url_decode($pre);
		if (!$data) {
			throw new NotFoundHttpException();
		}
		$split = explode(';', $data);
		if (count($split) !== 5) {
			throw new NotFoundHttpException();
		}
		list(
			$profiel->voornaam,
			$profiel->tussenvoegsel,
			$profiel->achternaam,
			$profiel->email,
			$profiel->mobiel
			) = $split;

		$form = new ExternProfielForm($profiel, '/inschrijven/' . $pre);
		if ($form->validate()) {
			$diff = $form->diff();
			$changeEntry = ProfielRepository::changelog($diff, LoginService::UID_EXTERN);
			foreach ($diff as $change) {
				if ($change->property === 'status') {
					array_push($changeEntry->entries, ...$this->profielRepository->wijzig_lidstatus($profiel, $change->old_value));
				}
			}
			$profiel->changelog[] = $changeEntry;

			$succes = false;

			try {
				/** @var Connection $conn */
				$conn = $this->getDoctrine()->getConnection();
				$conn->setAutoCommit(false);
				$conn->connect();
				try {
					$toestemmingForm = new ToestemmingModalForm($this->lidToestemmingRepository, true);

					// Sla toesteming op.
					if ($toestemmingForm->validate()) {
						$this->profielRepository->create($profiel);
						$this->lidToestemmingRepository->saveForLid($profiel->uid);
						$conn->commit();
						$succes = true;
					} else {
						throw new CsrException('Vul de toestemmingen in');
					}
				} catch (Exception $e) {
					setMelding($e->getMessage(), -1);
					if ($conn->isTransactionActive()) {
						$conn->rollBack();
					}
				} finally {
					$conn->setAutoCommit(true);
				}
			} catch (CsrException $ex) {
				setMelding($ex->getMessage(), -1);
			}

			if ($succes) {
				return $this->render('extern-inschrijven/tekstpagina.html.twig', [
					'titel' => 'C.S.R. Delft - Inschrijven',
					'content' => '
					<h1 class="Titel">Bedankt voor je inschrijving!</h1>
					<p>De NovCie neemt z.s.m. contact met je op.</p>
				']);
			}
		}

		return $this->render('extern-inschrijven/inschrijven.html.twig', ['titel' => 'C.S.R. Delft - Inschrijven', 'content' => $form]);
	}

	/**
	 * @return Response
	 * @Route("/profiel/voorkeuren", methods={"GET"})
	 * @Auth(P_PROFIEL_EDIT)
	 */
	public function voorkeurenNoUid(Request $request,
																	VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository,
																	CommissieVoorkeurRepository $commissieVoorkeurRepository,
																	VoorkeurCommissieRepository $voorkeurCommissieRepository): Response
	{
		return $this->voorkeuren($request, $voorkeurOpmerkingRepository, $commissieVoorkeurRepository, $voorkeurCommissieRepository, $this->getUid());
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Route("/profiel/{uid}/voorkeuren", methods={"GET", "POST"}, requirements={"uid": ".{4}"})
	 * @Auth(P_PROFIEL_EDIT)
	 * @CsrfUnsafe
	 */
	public function voorkeuren(
		Request $request,
		VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository,
		CommissieVoorkeurRepository $commissieVoorkeurRepository,
		VoorkeurCommissieRepository $voorkeurCommissieRepository,
		$uid
	): Response
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}
		if (!$profiel->magBewerken()) {
			throw $this->createAccessDeniedException();
		}
		$voorkeuren1 = new CommissieVoorkeuren();
		$voorkeuren1->opmerking = $voorkeurOpmerkingRepository->getOpmerkingVoorLid($profiel)->lidOpmerking;
		$categorieCommissie = $voorkeurCommissieRepository->getByCategorie();
		foreach ($categorieCommissie as $cat) {
			$categorie = $cat['categorie'];
			foreach ($cat['commissies'] as $commissie) {
				if ($commissie->zichtbaar) {
					$voorkeuren1->voorkeuren->add($commissieVoorkeurRepository->getVoorkeur($profiel, $commissie));
				}
			}
		}

		$form = $this->createForm(CommissieVoorkeurenType::class, $voorkeuren1, [
			'action' => $this->generateUrl('csrdelft_profiel_voorkeuren', ['uid' => $uid]),
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$voorkeuren = $voorkeuren1->getVoorkeuren();
			$opmerking = $voorkeurOpmerkingRepository->getOpmerkingVoorLid($profiel);
			$opmerking->lidOpmerking = $voorkeuren1->getOpmerking();
			$manager = $this->getDoctrine()->getManager();
			foreach ($voorkeuren as $voorkeur) {
				$manager->persist($voorkeur);
			}
			$manager->persist($opmerking);
			$manager->flush();
			setMelding('Voorkeuren opgeslagen', 1);
			return $this->redirectToRoute('csrdelft_profiel_voorkeuren', ['uid' => $uid]);
		}

		return $this->render('commissievoorkeuren/persoonlijk.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @param $uid
	 * @return RedirectResponse
	 * @Route("/profiel/{uid}/addToGoogleContacts", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function addToGoogleContacts($uid): RedirectResponse
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}
		try {
			$addToContactsUrl = $this->generateUrl('csrdelft_profiel_addtogooglecontacts', ['uid' => $profiel->uid], UrlGeneratorInterface::ABSOLUTE_URL);
			$this->googleSync->doRequestToken($addToContactsUrl);
			$msg = $this->googleSync->syncLid($profiel);
			setMelding('Opgeslagen in Google Contacts: ' . $msg, 1);
		} catch (CsrException $e) {
			setMelding("Opslaan in Google Contacts mislukt: " . $e->getMessage(), -1);
		}
		return $this->redirectToRoute('csrdelft_profiel_profiel', ['uid' => $profiel->uid]);
	}


	/**
	 * @param null $uid
	 * @return Response
	 * @Route("/profiel/{uid}/stamboom", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function stamboom($uid = null): Response
	{
		$profiel = $uid ? $this->profielRepository->get($uid) : $this->getProfiel();

		return $this->render('profiel/stamboom.html.twig', [
			'profiel' => $profiel,
		]);
	}

	/**
	 * @param VerjaardagenService $verjaardagenService
	 * @return Response
	 * @Route("/leden/verjaardagen", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function verjaardagen(VerjaardagenService $verjaardagenService): Response
	{
		$nu = time();
		return $this->render('verjaardagen/alle.html.twig', [
			'dezemaand' => date('m', $nu),
			'dezedag' => date('d', $nu),
			'verjaardagen' => $verjaardagenService->getJaar(),
		]);
	}

	/**
	 * @param $uid
	 * @param $timespan
	 * @param SaldoGrafiekService $saldoGrafiekService
	 * @return JsonResponse
	 * @throws Exception
	 * @Route("/profiel/{uid}/saldo/{timespan}", methods={"POST"}, requirements={"uid": ".{4}", "timespan": "\d+"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function saldo($uid, $timespan, SaldoGrafiekService $saldoGrafiekService): JsonResponse
	{
		if ($saldoGrafiekService->magGrafiekZien($uid)) {
			return new JsonResponse($saldoGrafiekService->getDataPoints($uid, $timespan));
		} else {
			throw $this->createAccessDeniedException();
		}
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Route("/profiel/{uid}.vcf", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function vcard($uid): Response
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		$response = new Response(null, 200, ['Content-Type' => 'text/x-vcard']);

		return $this->render('profiel/vcard.ical.twig', ['profiel' => $profiel], $response);
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Route("/profiel/{uid}/kaartje", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function kaartje($uid): Response
	{
		return $this->render('profiel/kaartje.html.twig', ['profiel' => $this->profielRepository->get($uid)]);
	}
}
