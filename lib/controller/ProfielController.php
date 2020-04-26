<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrNotFoundException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\GoogleSync;
use CsrDelft\entity\fotoalbum\Foto;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\SaldoGrafiekModel;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\bibliotheek\BoekExemplaarRepository;
use CsrDelft\repository\bibliotheek\BoekRecensieRepository;
use CsrDelft\repository\commissievoorkeuren\CommissieVoorkeurRepository;
use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\repository\corvee\CorveeKwalificatiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\corvee\CorveeVoorkeurenRepository;
use CsrDelft\repository\corvee\CorveeVrijstellingenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoRepository;
use CsrDelft\repository\fotoalbum\FotoTagsRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
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
	 * @var VoorkeurOpmerkingRepository
	 */
	private $voorkeurOpmerkingRepository;
	/**
	 * @var CommissieVoorkeurRepository
	 */
	private $commissieVoorkeurRepository;
	/**
	 * @var FotoTagsRepository
	 */
	private $fotoTagsRepository;
	/**
	 * @var FotoRepository
	 */
	private $fotoRepository;
	/**
	 * @var BesturenModel
	 */
	private $besturenModel;
	/**
	 * @var CommissiesModel
	 */
	private $commissiesModel;
	/**
	 * @var BoekRecensieRepository
	 */
	private $boekRecensieModel;
	/**
	 * @var MaaltijdAbonnementenRepository
	 */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var BoekExemplaarRepository
	 */
	private $boekExemplaarModel;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var CorveeKwalificatiesRepository
	 */
	private $corveeKwalificatiesRepository;
	/**
	 * @var CorveeVrijstellingenRepository
	 */
	private $corveeVrijstellingenRepository;
	/**
	 * @var CorveeVoorkeurenRepository
	 */
	private $corveeVoorkeurenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var CiviBestellingModel
	 */
	private $civiBestellingModel;
	/**
	 * @var ActiviteitenModel
	 */
	private $activiteitenModel;
	/**
	 * @var KetzersModel
	 */
	private $ketzersModel;
	/**
	 * @var RechtenGroepenModel
	 */
	private $rechtenGroepenModel;
	/**
	 * @var OnderverenigingenModel
	 */
	private $onderverenigingenModel;
	/**
	 * @var WerkgroepenModel
	 */
	private $werkgroepenModel;
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var SaldoGrafiekModel
	 */
	private $saldoGrafiekModel;
	/**
	 * @var VerjaardagenService
	 */
	private $verjaardagenService;

	public function __construct(
		ProfielRepository $profielRepository,
		AccountRepository $accountRepository,
		ActiviteitenModel $activiteitenModel,
		BesturenModel $besturenModel,
		BoekExemplaarRepository $boekExemplaarModel,
		BoekRecensieRepository $boekRecensieModel,
		CiviBestellingModel $civiBestellingModel,
		CommissieVoorkeurRepository $commissieVoorkeurRepository,
		CorveeVoorkeurenRepository $corveeVoorkeurenRepository,
		CommissiesModel $commissiesModel,
		CorveeTakenRepository $corveeTakenRepository,
		CorveeVrijstellingenRepository $corveeVrijstellingenRepository,
		ForumPostsRepository $forumPostsRepository,
		FotoRepository $fotoRepository,
		FotoTagsRepository $fotoTagsRepository,
		KetzersModel $ketzersModel,
		CorveeKwalificatiesRepository $corveeKwalificatiesRepository,
		LidToestemmingRepository $lidToestemmingRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		OnderverenigingenModel $onderverenigingenModel,
		RechtenGroepenModel $rechtenGroepenModel,
		VoorkeurOpmerkingRepository $voorkeurOpmerkingRepository,
		WerkgroepenModel $werkgroepenModel,
		SaldoGrafiekModel $saldoGrafiekModel,
		VerjaardagenService $verjaardagenService
	) {
		$this->profielRepository = $profielRepository;
		$this->accountRepository = $accountRepository;
		$this->activiteitenModel = $activiteitenModel;
		$this->besturenModel = $besturenModel;
		$this->boekExemplaarModel = $boekExemplaarModel;
		$this->boekRecensieModel = $boekRecensieModel;
		$this->civiBestellingModel = $civiBestellingModel;
		$this->commissieVoorkeurRepository = $commissieVoorkeurRepository;
		$this->commissiesModel = $commissiesModel;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->corveeVoorkeurenRepository = $corveeVoorkeurenRepository;
		$this->corveeVrijstellingenRepository = $corveeVrijstellingenRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->fotoRepository = $fotoRepository;
		$this->fotoTagsRepository = $fotoTagsRepository;
		$this->ketzersModel = $ketzersModel;
		$this->corveeKwalificatiesRepository = $corveeKwalificatiesRepository;
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->onderverenigingenModel = $onderverenigingenModel;
		$this->rechtenGroepenModel = $rechtenGroepenModel;
		$this->voorkeurOpmerkingRepository = $voorkeurOpmerkingRepository;
		$this->werkgroepenModel = $werkgroepenModel;
		$this->saldoGrafiekModel = $saldoGrafiekModel;
		$this->verjaardagenService = $verjaardagenService;
	}

	public function resetPrivateToken($uid) {
		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		if ($profiel->account == null) {
			throw new CsrNotFoundException("Profiel heeft geen account");
		}

		$this->accountRepository->resetPrivateToken($profiel->account);
		return $this->profiel($uid);
	}

	public function profiel($uid) {
		if ($uid == null) {
			$uid = LoginModel::getUid();
		}

		$profiel = $this->profielRepository->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		$fotos = [];
		foreach ($this->fotoTagsRepository->findBy(['keyword' => $uid], null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = $this->fotoRepository->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}

		return view('profiel.profiel', [
			'profiel' => $profiel,
			'besturen' => $this->besturenModel->getGroepenVoorLid($uid),
			'commissies' => $this->commissiesModel->getGroepenVoorLid($uid),
			'werkgroepen' => $this->werkgroepenModel->getGroepenVoorLid($uid),
			'onderverenigingen' => $this->onderverenigingenModel->getGroepenVoorLid($uid),
			'groepen' => $this->rechtenGroepenModel->getGroepenVoorLid($uid),
			'ketzers' => $this->ketzersModel->getGroepenVoorLid($uid),
			'activiteiten' => $this->activiteitenModel->getGroepenVoorLid($uid),
			'bestellinglog' => $this->civiBestellingModel->getBeschrijving($this->civiBestellingModel->getBestellingenVoorLid($uid, 10)->fetchAll()),
			'bestellingenlink' => '/fiscaat/bestellingen' . (LoginModel::getUid() === $uid ? '' : '/' . $uid),
			'corveetaken' => $this->corveeTakenRepository->getTakenVoorLid($uid),
			'corveevoorkeuren' => $this->corveeVoorkeurenRepository->getVoorkeurenVoorLid($uid),
			'corveevrijstelling' => $this->corveeVrijstellingenRepository->getVrijstelling($uid),
			'corveekwalificaties' => $this->corveeKwalificatiesRepository->getKwalificatiesVanLid($uid),
			'forumpostcount' => $this->forumPostsRepository->getAantalForumPostsVoorLid($uid),
			'forumrecent' => $this->forumPostsRepository->getRecenteForumPostsVanLid($uid, (int)lid_instelling('forum', 'draden_per_pagina')),
			'boeken' => $this->boekExemplaarModel->getEigendom($uid),
			'recenteAanmeldingen' => $this->maaltijdAanmeldingenRepository->getRecenteAanmeldingenVoorLid($uid, date_create_immutable(instelling('maaltijden', 'recent_lidprofiel'))),
			'abos' => $this->maaltijdAbonnementenRepository->getAbonnementenVoorLid($uid),
			'gerecenseerdeboeken' => $this->boekRecensieModel->getVoorLid($uid),
			'fotos' => $fotos
		]);
	}

	public function nieuw($lidjaar, $status) {
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) || !in_array($lidstatus, LidStatus::getTypeOptions())) {
			throw new CsrToegangException();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet && !LoginModel::mag(P_LEDEN_MOD)) {
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
				$nieuw = !$this->profielRepository->exists($profiel);
				$changeEntry = ProfielRepository::changelog($diff, LoginModel::getUid());
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
			$gSync = GoogleSync::instance();
			$msg = $gSync->syncLid($profiel);
			setMelding('Opgeslagen in Google Contacts: ' . $msg, 1);
		} catch (CsrException $e) {
			setMelding("Opslaan in Google Contacts mislukt: " . $e->getMessage(), -1);
		}
		return $this->redirectToRoute('profiel-profiel', ['uid' => $profiel->uid]);
	}


	public function stamboom($uid = null) {
		return view('profiel.stamboom', [
			'profiel' => ProfielRepository::get($uid) ?? LoginModel::getProfiel(),
		]);
	}

	public function verjaardagen() {
		$nu = time();
		return view('verjaardagen.alle', [
			'dezemaand' => date('n', $nu),
			'dezedag' => date('d', $nu),
			'verjaardagen' => $this->verjaardagenService->getJaar(),
		]);
	}

	public function saldo($uid, $timespan) {
		if ($this->saldoGrafiekModel->magGrafiekZien($uid)) {
			return new JsonResponse($this->saldoGrafiekModel->getDataPoints($uid, $timespan));
		} else {
			throw new CsrToegangException();
		}
	}

	public function vcard($uid) {
		$profiel = ProfielRepository::get($uid);

		if (!$profiel) {
			throw new CsrNotFoundException();
		}

		return new VcardResponse(view('profiel.vcard', [
			'profiel' => $profiel,
		])->toString());
	}

	public function kaartje($uid) {
		return view('profiel.kaartje', ['profiel' => ProfielRepository::get($uid)]);
	}

	public function redirectWithUid($route) {
		return $this->redirectToRoute($route, ['uid' => LoginModel::getUid()]);
	}
}
