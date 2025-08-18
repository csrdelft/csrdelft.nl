<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\CsrException;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\UrlUtil;
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
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\fiscaat\SaldoGrafiekService;
use CsrDelft\service\GoogleContactSync;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use CsrDelft\service\maalcie\MaaltijdAbonnementenService;
use CsrDelft\service\profiel\LidStatusService;
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
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use chillerlan\QRCode\QRCode;

class ProfielController extends AbstractController
{
	public function __construct(
		private readonly ProfielRepository $profielRepository,
		private readonly LidStatusService $lidStatusService,
		private readonly AccountRepository $accountRepository,
		private readonly LidToestemmingRepository $lidToestemmingRepository
	) {
	}

	/**
	 * @param $uid
	 * @return RedirectResponse
	 * @Auth(P_PROFIEL_EDIT)
	 */
	#[
		Route(
			path: '/profiel/{uid}/resetPrivateToken',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function resetPrivateToken($uid): RedirectResponse
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		if ($profiel->account == null) {
			throw new NotFoundHttpException('Profiel heeft geen account');
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
	 * @param MaaltijdAanmeldingenService $maaltijdAanmeldingenService
	 * @param CorveeVrijstellingenRepository $corveeVrijstellingenRepository
	 * @param ForumPostsRepository $forumPostsRepository
	 * @param FotoTagsRepository $fotoTagsRepository
	 * @param CorveeKwalificatiesRepository $corveeKwalificatiesRepository
	 * @param MaaltijdAbonnementenService $maaltijdAbonnementenService
	 * @param Profiel|null $profiel
	 * @return Response
	 * @throws Throwable
	 * @Auth(P_OUDLEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}',
			methods: ['GET'],
			defaults: ['uid' => null],
			requirements: ['uid' => '.{4}']
		)
	]
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
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		CorveeVrijstellingenRepository $corveeVrijstellingenRepository,
		ForumPostsRepository $forumPostsRepository,
		FotoTagsRepository $fotoTagsRepository,
		CorveeKwalificatiesRepository $corveeKwalificatiesRepository,
		MaaltijdAbonnementenService $maaltijdAbonnementenService,
		Profiel $profiel = null
	): Response {
		if (!$profiel) {
			$profiel = $this->getProfiel();
		}
		$fotos = [];
		foreach (
			$fotoTagsRepository->findBy(['keyword' => $profiel->uid], null, 3)
			as $tag
		) {
			/** @var Foto $foto */
			$foto = $fotoRepository->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}

		return $this->render('profiel/profiel.html.twig', [
			'profiel' => $profiel,
			'besturen' => $besturenRepository->getGroepenVoorLid($profiel),
			'commissies_ft' => $commissiesRepository->getGroepenVoorLid(
				$profiel,
				GroepStatus::FT
			),
			'commissies_ht' => $commissiesRepository->getGroepenVoorLid(
				$profiel,
				GroepStatus::HT
			),
			'commissies_ot' => $commissiesRepository->getGroepenVoorLid(
				$profiel,
				GroepStatus::OT
			),
			'werkgroepen' => $werkgroepenRepository->getGroepenVoorLid($profiel),
			'onderverenigingen' => $onderverenigingenRepository->getGroepenVoorLid(
				$profiel
			),
			'groepen' => $rechtenGroepenRepository->getGroepenVoorLid($profiel),
			'ketzers' => $ketzersRepository->getGroepenVoorLid($profiel),
			'activiteiten' => $activiteitenRepository->getGroepenVoorLid($profiel),
			'bestellinglog' => $civiBestellingRepository->getBestellingenVoorLid(
				$profiel->uid,
				10
			),
			'bestellingenlink' =>
				'/fiscaat/bestellingen' .
				($this->getUid() === $profiel->uid ? '' : '/' . $profiel->uid),
			'corveetaken' => $corveeTakenRepository->getTakenVoorLid($profiel),
			'corveevoorkeuren' => $corveeVoorkeurenRepository->getVoorkeurenVoorLid(
				$profiel->uid
			),
			'corveevrijstelling' => $corveeVrijstellingenRepository->getVrijstelling(
				$profiel->uid
			),
			'corveekwalificaties' => $corveeKwalificatiesRepository->getKwalificatiesVanLid(
				$profiel->uid
			),
			'forumpostcount' => $forumPostsRepository->getAantalForumPostsVoorLid(
				$profiel->uid
			),
			'forumrecent' => $forumPostsRepository->getRecenteForumPostsVanLid(
				$profiel->uid,
				(int) InstellingUtil::lid_instelling('forum', 'draden_per_pagina')
			),
			'boeken' => $boekExemplaarRepository->getEigendom($profiel->uid),
			'recenteAanmeldingen' => $maaltijdAanmeldingenService->getRecenteAanmeldingenVoorLid(
				$profiel->uid,
				date_create_immutable(
					InstellingUtil::instelling('maaltijden', 'recent_lidprofiel')
				)
			),
			'abos' => $maaltijdAbonnementenService->getAbonnementenVoorLid($profiel),
			'gerecenseerdeboeken' => $boekRecensieRepository->getVoorLid(
				$profiel->uid
			),
			'fotos' => $fotos,
		]);
	}

	/**
	 * @param $lidjaar
	 * @param $status
	 * @param EntityManagerInterface $em
	 * @return RedirectResponse|Response
	 * @Auth({P_LEDEN_MOD,"commissie:NovCie"})
	 * @CsrfUnsafe()
	 */
	#[
		Route(
			path: '/profiel/{lidjaar}/nieuw/{status}',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function nieuw($lidjaar, $status, EntityManagerInterface $em)
	{
		if ($em->getFilters()->isEnabled('verbergNovieten')) {
			$em->getFilters()->disable('verbergNovieten');
		}
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper((string) $status);
		if (
			!preg_match('/^[0-9]{4}$/', (string) $lidjaar) ||
			!in_array($lidstatus, LidStatus::getEnumValues())
		) {
			throw $this->createAccessDeniedException();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet && !$this->mag(P_LEDEN_MOD)) {
			throw $this->createAccessDeniedException();
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = $this->profielRepository->nieuw((int) $lidjaar, $lidstatus);

		return $this->profielBewerken($profiel, true);
	}

	private function profielBewerken(Profiel $profiel, $alleenFormulier = false)
	{
		if (!$profiel->magBewerken()) {
			throw $this->createAccessDeniedException();
		}
		$form = new ProfielForm($profiel, $alleenFormulier);
		if ($form->validate()) {
			$diff = $form->diff();
			if (empty($diff)) {
				$this->addFlash(FlashType::INFO, 'Geen wijzigingen');
			} else {
				$nieuw =
					$profiel->uid === null ||
					$this->profielRepository->find($profiel->uid) == null;
				$changeEntry = ProfielRepository::changelog($diff, $this->getUid());
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						array_push(
							$changeEntry->entries,
							...$this->lidStatusService->wijzig_lidstatus(
								$profiel,
								$change->old_value
							)
						);
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
								$toestemmingForm = new ToestemmingModalForm(
									$this->lidToestemmingRepository,
									true
								);
								if ($toestemmingForm->validate()) {
									$this->lidToestemmingRepository->saveForLid($profiel->uid);
								} else {
									throw new CsrException('Opslaan van toestemming mislukt');
								}
							}
							$conn->commit();
						} catch (Exception $e) {
							$this->addFlash(FlashType::ERROR, $e->getMessage());
							$conn->rollBack();
						} finally {
							$conn->setAutoCommit(true);
						}
					} catch (CsrException $ex) {
						$this->addFlash(FlashType::ERROR, $ex->getMessage());
					}

					$this->addFlash(
						FlashType::SUCCESS,
						'Profiel succesvol opgeslagen met lidnummer: ' . $profiel->uid
					);
				} else {
					$this->profielRepository->update($profiel);
					$this->addFlash(
						FlashType::SUCCESS,
						count($diff) . ' wijziging(en) succesvol opgeslagen'
					);
				}
			}
			return $this->redirectToRoute('csrdelft_profiel_profiel', [
				'uid' => $profiel->uid,
			]);
		}
		if ($alleenFormulier) {
			return $this->render('plain.html.twig', [
				'titel' => 'Noviet toevoegen',
				'content' => $form,
			]);
		}
		return $this->render('default.html.twig', ['content' => $form]);
	}

	/**
	 * @param $uid
	 * @return RedirectResponse|Response
	 * @Auth(P_PROFIEL_EDIT)
	 */
	#[
		Route(
			path: '/profiel/{uid}/bewerken',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function bewerken($uid)
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		return $this->profielBewerken($profiel);
	}

	/**
	 * @Auth({P_LEDEN_MOD,"commissie:NovCie"})
	 * @return Response
	 */
	#[
		Route(
			path: '/inschrijflink',
			methods: ['GET', 'POST'],
			name: 'inschrijflink'
		)
	]
	public function externInschrijfLink(): Response
	{
		$form = new InschrijfLinkForm();
		$link = null;

		if ($form->validate()) {
			$values = $form->getValues();

			$payload = [
				'voornaam'       => $values['voornaam'],
				'tussenvoegsel'  => $values['tussenvoegsel'],
				'achternaam'     => $values['achternaam'],
				'email'          => $values['email'],
				'mobiel'         => $values['mobiel'],
				'exp'            => time() + 7 * 24 * 60 * 60 // 1 week
			];

			$jwt = JWT::encode($payload, $_ENV['INSCHRIJFLINK_SECRET'], 'HS256');

			$link = $this->generateUrl(
				'extern-inschrijven',
				['pre' => $jwt],
				UrlGeneratorInterface::ABSOLUTE_URL
			);

			$_POST = [];
			$form = new InschrijfLinkForm();
		}

		return $this->render('extern-inschrijven/link.html.twig', [
			'link' => $link,
			'qrcode' => (new QRCode)->render($link),
			'form' => $form,
		]);
	}

	/**
	 * @Auth(P_PUBLIC)
	 * @CsrfUnsafe()
	 * @param string $pre
	 * @param EntityManagerInterface $em
	 * @return Response
	 * @throws ConnectionException
	 */
	#[
		Route(
			path: '/inschrijven/{pre}',
			methods: ['GET', 'POST'],
			name: 'extern-inschrijven'
		)
	]
	public function externInschrijfformulier(
		string $pre,
		EntityManagerInterface $em
	): Response {
		if (DateUtil::isDatumVoorbij('2025-08-21 00:00:00')) {
			return $this->render('extern-inschrijven/tekstpagina.html.twig', [
				'titel' => 'C.S.R. Delft - Inschrijven',
				'content' => '
				<h1 class="Titel">Inschrijvingen gesloten</h1>
				<p>Neem contact op met <a href="mailto:novcie@csrdelft.nl">novcie@csrdelft.nl</a></p>
			',
			]);
		}

		if ($em->getFilters()->isEnabled('verbergNovieten')) {
			$em->getFilters()->disable('verbergNovieten');
		}

		$profiel = $this->profielRepository->nieuw(
			date_create_immutable()->format('Y'),
			LidStatus::Noviet
		);

		if (empty($pre)) {
			throw new NotFoundHttpException();
		}

		try {
			$decoded = (array) JWT::decode($pre, new Key($_ENV['INSCHRIJFLINK_SECRET'], 'HS256'));
		} catch (ExpiredException $e) {
			throw new NotFoundHttpException('Link expired');
		} catch (Exception $e) {
			throw new NotFoundHttpException('Invalid link');
		}

		$profiel->voornaam      = $decoded['voornaam'] ?? '';
		$profiel->tussenvoegsel = $decoded['tussenvoegsel'] ?? '';
		$profiel->achternaam    = $decoded['achternaam'] ?? '';
		$profiel->email         = $decoded['email'] ?? '';
		$profiel->mobiel        = $decoded['mobiel'] ?? '';

		$form = new ExternProfielForm($profiel, '/inschrijven/' . $pre);
		if ($form->validate()) {
			$diff = $form->diff();
			$changeEntry = ProfielRepository::changelog(
				$diff,
				LoginService::UID_EXTERN
			);
			foreach ($diff as $change) {
				if ($change->property === 'status') {
					array_push(
						$changeEntry->entries,
						...$this->lidStatusService->wijzig_lidstatus(
						$profiel,
						$change->old_value
					)
					);
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
					$toestemmingForm = new ToestemmingModalForm(
						$this->lidToestemmingRepository,
						true
					);

					if ($toestemmingForm->validate()) {
						$this->profielRepository->create($profiel);
						$this->lidToestemmingRepository->saveForLid($profiel->uid);
						$conn->commit();
						$succes = true;
					} else {
						throw new CsrException('Vul de toestemmingen in');
					}
				} catch (Exception $e) {
					$this->addFlash(FlashType::ERROR, $e->getMessage());
					if ($conn->isTransactionActive()) {
						$conn->rollBack();
					}
				} finally {
					$conn->setAutoCommit(true);
				}
			} catch (CsrException $ex) {
				$this->addFlash(FlashType::ERROR, $ex->getMessage());
			}

			if ($succes) {
				return $this->render('extern-inschrijven/tekstpagina.html.twig', [
					'titel' => 'C.S.R. Delft - Inschrijven',
					'content' => '
                    <h1 class="Titel">Bedankt voor je inschrijving!</h1>
                    <p>De NovCie neemt z.s.m. contact met je op.</p>
                ',
				]);
			}
		}

		return $this->render('extern-inschrijven/inschrijven.html.twig', [
			'titel' => 'C.S.R. Delft - Inschrijven',
			'content' => $form,
		]);
	}

	/**
	 * @return Response
	 * @Auth(P_PROFIEL_EDIT)
	 */
	#[Route(path: '/profiel/voorkeuren', methods: ['GET'])]
	public function voorkeurenNoUid(
		Request $request,
		VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository,
		CommissieVoorkeurRepository $commissieVoorkeurRepository,
		VoorkeurCommissieRepository $voorkeurCommissieRepository
	): Response {
		return $this->voorkeuren(
			$request,
			$voorkeurOpmerkingRepository,
			$commissieVoorkeurRepository,
			$voorkeurCommissieRepository,
			$this->getUid()
		);
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Auth(P_PROFIEL_EDIT)
	 * @CsrfUnsafe
	 */
	#[
		Route(
			path: '/profiel/{uid}/voorkeuren',
			methods: ['GET', 'POST'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function voorkeuren(
		Request $request,
		VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository,
		CommissieVoorkeurRepository $commissieVoorkeurRepository,
		VoorkeurCommissieRepository $voorkeurCommissieRepository,
		$uid
	): Response {
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}
		if (!$profiel->magBewerken()) {
			throw $this->createAccessDeniedException();
		}

		$opmerking = $voorkeurOpmerkingRepository->getOpmerkingVoorLid($profiel);
		$categorieCommissie = $voorkeurCommissieRepository->getByCategorie();
		$voorkeuren = [];
		foreach ($categorieCommissie as $cat) {
			foreach ($cat['commissies'] as $commissie) {
				if ($commissie->zichtbaar) {
					$voorkeuren[] = $commissieVoorkeurRepository->getVoorkeur(
						$profiel,
						$commissie
					);
				}
			}
		}

		$form = $this->createForm(CommissieVoorkeurenType::class, $opmerking, [
			'action' => $this->generateUrl('csrdelft_profiel_voorkeuren', [
				'uid' => $uid,
			]),
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($opmerking);
			$manager->flush();
			$this->addFlash(FlashType::SUCCESS, 'Voorkeuren opgeslagen');
			return $this->redirectToRoute('csrdelft_profiel_voorkeuren', [
				'uid' => $uid,
			]);
		}

		return $this->render('commissievoorkeuren/persoonlijk.html.twig', [
			'form' => $form->createView(),
			'uid' => $uid,
			'voorkeuren' => $voorkeuren,
		]);
	}

	/**
	 * @param $uid
	 * @param GoogleContactSync $googleContactSync
	 * @return RedirectResponse
	 * @Auth(P_LEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}/addToGoogleContacts',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function addToGoogleContacts(
		$uid,
		GoogleContactSync $googleContactSync
	): RedirectResponse {
		$profiel = $this->profielRepository->find($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		$addToContactsUrl = $this->generateUrl(
			'csrdelft_profiel_addtogooglecontacts',
			['uid' => $profiel->uid],
			UrlGeneratorInterface::ABSOLUTE_URL
		);
		$googleContactSync->initialize($addToContactsUrl);
		$msg = $googleContactSync->syncLid($profiel);
		$this->addFlash(
			FlashType::SUCCESS,
			'Opgeslagen in Google Contacten: ' . $msg
		);
		return $this->redirectToRoute('csrdelft_profiel_profiel', [
			'uid' => $profiel->uid,
		]);
	}

	/**
	 * @param null $uid
	 * @return Response
	 * @Auth(P_OUDLEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}/stamboom',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
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
	 * @Auth(P_OUDLEDEN_READ)
	 */
	#[Route(path: '/leden/verjaardagen', methods: ['GET'])]
	public function verjaardagen(
		VerjaardagenService $verjaardagenService
	): Response {
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
	 * @Auth(P_LEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}/saldo/{timespan}',
			methods: ['POST'],
			requirements: ['uid' => '.{4}', 'timespan' => '\d+']
		)
	]
	public function saldo(
		$uid,
		$timespan,
		SaldoGrafiekService $saldoGrafiekService
	): JsonResponse {
		if ($saldoGrafiekService->magGrafiekZien($uid)) {
			return new JsonResponse(
				$saldoGrafiekService->getDataPoints($uid, $timespan)
			);
		} else {
			throw $this->createAccessDeniedException();
		}
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Auth(P_LEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}.vcf',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function vcard($uid): Response
	{
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new NotFoundHttpException();
		}

		$response = new Response(null, 200, ['Content-Type' => 'text/x-vcard']);

		return $this->render(
			'profiel/vcard.ical.twig',
			['profiel' => $profiel],
			$response
		);
	}

	/**
	 * @param $uid
	 * @return Response
	 * @Auth(P_LEDEN_READ)
	 */
	#[
		Route(
			path: '/profiel/{uid}/kaartje',
			methods: ['GET'],
			requirements: ['uid' => '.{4}']
		)
	]
	public function kaartje($uid): Response
	{
		return $this->render('profiel/kaartje.html.twig', [
			'profiel' => $this->profielRepository->get($uid),
		]);
	}
}
