<?php

namespace CsrDelft\controller;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrNotFoundException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
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
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenForm;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\profiel\ProfielForm;
use CsrDelft\view\response\VcardResponse;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Doctrine\DBAL\Connection;
use Exception;

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

	public function __construct(
		ProfielRepository $profielRepository,
		AccountRepository $accountRepository,
		LidToestemmingRepository $lidToestemmingRepository
	) {
		$this->profielRepository = $profielRepository;
		$this->accountRepository = $accountRepository;
		$this->lidToestemmingRepository = $lidToestemmingRepository;
	}

	public function resetPrivateToken($uid) {
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new CsrNotFoundException();
		}

		if ($profiel->account == null) {
			throw new CsrNotFoundException("Profiel heeft geen account");
		}

		$this->accountRepository->resetPrivateToken($profiel->account);

		return $this->redirectToRoute('profiel-profiel', ['uid' => $uid]);
	}

	public function profiel(
		$uid,
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
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository
	) {
		if ($uid == null) {
			$uid = LoginService::getUid();
		}

		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		$fotos = [];
		foreach ($fotoTagsRepository->findBy(['keyword' => $uid], null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = $fotoRepository->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}

		return view('profiel.profiel', [
			'profiel' => $profiel,
			'besturen' => $besturenRepository->getGroepenVoorLid($uid),
			'commissies' => $commissiesRepository->getGroepenVoorLid($uid),
			'werkgroepen' => $werkgroepenRepository->getGroepenVoorLid($uid),
			'onderverenigingen' => $onderverenigingenRepository->getGroepenVoorLid($uid),
			'groepen' => $rechtenGroepenRepository->getGroepenVoorLid($uid),
			'ketzers' => $ketzersRepository->getGroepenVoorLid($uid),
			'activiteiten' => $activiteitenRepository->getGroepenVoorLid($uid),
			'bestellinglog' => $civiBestellingRepository->getBestellingenVoorLid($uid, 10),
			'bestellingenlink' => '/fiscaat/bestellingen' . (LoginService::getUid() === $uid ? '' : '/' . $uid),
			'corveetaken' => $corveeTakenRepository->getTakenVoorLid($uid),
			'corveevoorkeuren' => $corveeVoorkeurenRepository->getVoorkeurenVoorLid($uid),
			'corveevrijstelling' => $corveeVrijstellingenRepository->getVrijstelling($uid),
			'corveekwalificaties' => $corveeKwalificatiesRepository->getKwalificatiesVanLid($uid),
			'forumpostcount' => $forumPostsRepository->getAantalForumPostsVoorLid($uid),
			'forumrecent' => $forumPostsRepository->getRecenteForumPostsVanLid($uid, (int)lid_instelling('forum', 'draden_per_pagina')),
			'boeken' => $boekExemplaarRepository->getEigendom($uid),
			'recenteAanmeldingen' => $maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid($uid, date_create_immutable(instelling('maaltijden', 'recent_lidprofiel'))),
			'abos' => $maaltijdAbonnementenRepository->getAbonnementenVoorLid($uid),
			'gerecenseerdeboeken' => $boekRecensieRepository->getVoorLid($uid),
			'fotos' => $fotos
		]);
	}

	public function nieuw($lidjaar, $status) {
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) || !in_array($lidstatus, LidStatus::getEnumValues())) {
			throw new CsrToegangException();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet && !LoginService::mag(P_LEDEN_MOD)) {
			throw new CsrToegangException();
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = $this->profielRepository->nieuw((int)$lidjaar, $lidstatus);

		return $this->profielBewerken($profiel, true);
	}

	private function profielBewerken(Profiel $profiel, $alleenFormulier = false) {

		if (!$profiel->magBewerken()) {
			throw new CsrToegangException();
		}
		$form = new ProfielForm($profiel);
		if ($form->validate()) {
			$diff = $form->diff();
			if (empty($diff)) {
				setMelding('Geen wijzigingen', 0);
			} else {
				$nieuw = $this->profielRepository->find($profiel->uid) == null;
				$changeEntry = ProfielRepository::changelog($diff, LoginService::getUid());
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
			return $this->redirectToRoute('profiel-profiel', ['uid' => $profiel->uid]);
		}
		if ($alleenFormulier) {
			return view('plain', ['titel' => 'Noviet toevoegen', 'content' => $form]);
		}
		return view('default', ['content' => $form]);
	}

	public function bewerken($uid) {
		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		return $this->profielBewerken($profiel);
	}

	public function voorkeuren($uid) {
		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}
		if (!$profiel->magBewerken()) {
			throw new CsrToegangException();
		}
		$form = new CommissieVoorkeurenForm($profiel);
		if ($form->isPosted() && $form->validate()) {
			$voorkeuren = $form->getVoorkeuren();
			$opmerking = $form->getOpmerking();
			$manager = $this->getDoctrine()->getManager();
			foreach ($voorkeuren as $voorkeur) {
				$manager->persist($voorkeur);
			}
			$manager->persist($opmerking);
			$manager->flush();
			setMelding('Voorkeuren opgeslagen', 1);
			$this->redirectToRoute('profiel-voorkeuren', ['uid' => $uid]);

		}
		return view('default', ['content' => $form]);
	}

	public function addToGoogleContacts($uid) {
		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}
		try {
			GoogleSync::doRequestToken(CSR_ROOT . "/profiel/" . $profiel->uid . "/addToGoogleContacts");
			$gSync = ContainerFacade::getContainer()->get(GoogleSync::class);
			$msg = $gSync->syncLid($profiel);
			setMelding('Opgeslagen in Google Contacts: ' . $msg, 1);
		} catch (CsrException $e) {
			setMelding("Opslaan in Google Contacts mislukt: " . $e->getMessage(), -1);
		}
		return $this->redirectToRoute('profiel-profiel', ['uid' => $profiel->uid]);
	}


	public function stamboom($uid = null) {
		$profiel = $uid ? $this->profielRepository->get($uid) : LoginService::getProfiel();

		return view('profiel.stamboom', [
			'profiel' => $profiel,
		]);
	}

	public function verjaardagen(VerjaardagenService $verjaardagenService) {
		$nu = time();
		return view('verjaardagen.alle', [
			'dezemaand' => date('n', $nu),
			'dezedag' => date('d', $nu),
			'verjaardagen' => $verjaardagenService->getJaar(),
		]);
	}

	public function saldo($uid, $timespan, SaldoGrafiekService $saldoGrafiekService) {
		if ($saldoGrafiekService->magGrafiekZien($uid)) {
			return new JsonResponse($saldoGrafiekService->getDataPoints($uid, $timespan));
		} else {
			throw new CsrToegangException();
		}
	}

	public function vcard($uid) {
		$profiel = $this->profielRepository->get($uid);

		if (!$profiel) {
			throw new CsrNotFoundException();
		}

		return new VcardResponse(view('profiel.vcard', ['profiel' => $profiel])->toString());
	}

	public function kaartje($uid) {
		return view('profiel.kaartje', ['profiel' => $this->profielRepository->get($uid)]);
	}

	public function redirectWithUid($route) {
		return $this->redirectToRoute($route, ['uid' => LoginService::getUid()]);
	}
}
