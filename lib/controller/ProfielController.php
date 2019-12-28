<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrNotFoundException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\GoogleSync;
use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\SaldoGrafiekModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\fotoalbum\FotoModel;
use CsrDelft\model\fotoalbum\FotoTagsModel;
use CsrDelft\model\groepen\ActiviteitenModel;
use CsrDelft\model\groepen\BesturenModel;
use CsrDelft\model\groepen\CommissiesModel;
use CsrDelft\model\groepen\KetzersModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\instellingen\LidToestemmingModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\maalcie\CorveeVrijstellingenModel;
use CsrDelft\model\maalcie\KwalificatiesModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdAbonnementenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\VerjaardagenModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenForm;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\profiel\ProfielForm;
use CsrDelft\view\response\VcardResponse;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ProfielController extends AbstractController {
	/**
	 * @var ProfielModel
	 */
	private $profielModel;
	/**
	 * @var VoorkeurOpmerkingModel
	 */
	private $voorkeurOpmerkingModel;
	/**
	 * @var CommissieVoorkeurModel
	 */
	private $commissieVoorkeurModel;
	/**
	 * @var FotoTagsModel
	 */
	private $fotoTagsModel;
	/**
	 * @var FotoModel
	 */
	private $fotoModel;
	/**
	 * @var BesturenModel
	 */
	private $besturenModel;
	/**
	 * @var CommissiesModel
	 */
	private $commissiesModel;
	/**
	 * @var BoekRecensieModel
	 */
	private $boekRecensieModel;
	/**
	 * @var MaaltijdAbonnementenModel
	 */
	private $maaltijdAbonnementenModel;
	/**
	 * @var MaaltijdAanmeldingenModel
	 */
	private $maaltijdAanmeldingenModel;
	/**
	 * @var BoekExemplaarModel
	 */
	private $boekExemplaarModel;
	/**
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;
	/**
	 * @var KwalificatiesModel
	 */
	private $kwalificatiesModel;
	/**
	 * @var CorveeVrijstellingenModel
	 */
	private $corveeVrijstellingenModel;
	/**
	 * @var CorveeVoorkeurenModel
	 */
	private $corveeVoorkeurenModel;
	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;
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
	 * @var LidToestemmingModel
	 */
	private $lidToestemmingModel;
	/**
	 * @var AccountModel
	 */
	private $accountModel;
	/**
	 * @var SaldoGrafiekModel
	 */
	private $saldoGrafiekModel;

	public function __construct(
		ProfielModel $profielModel,
		AccountModel $accountModel,
		ActiviteitenModel $activiteitenModel,
		BesturenModel $besturenModel,
		BoekExemplaarModel $boekExemplaarModel,
		BoekRecensieModel $boekRecensieModel,
		CiviBestellingModel $civiBestellingModel,
		CommissieVoorkeurModel $commissieVoorkeurModel,
		CorveeVoorkeurenModel $corveeVoorkeurenModel,
		CommissiesModel $commissiesModel,
		CorveeTakenModel $corveeTakenModel,
		CorveeVrijstellingenModel $corveeVrijstellingenModel,
		ForumPostsModel $forumPostsModel,
		FotoModel $fotoModel,
		FotoTagsModel $fotoTagsModel,
		KetzersModel $ketzersModel,
		KwalificatiesModel $kwalificatiesModel,
		LidToestemmingModel $lidToestemmingModel,
		MaaltijdAanmeldingenModel $maaltijdAanmeldingenModel,
		MaaltijdAbonnementenModel $maaltijdAbonnementenModel,
		OnderverenigingenModel $onderverenigingenModel,
		RechtenGroepenModel $rechtenGroepenModel,
		VoorkeurOpmerkingModel $voorkeurOpmerkingModel,
		WerkgroepenModel $werkgroepenModel,
		SaldoGrafiekModel $saldoGrafiekModel
	) {
		$this->profielModel = $profielModel;
		$this->accountModel = $accountModel;
		$this->activiteitenModel = $activiteitenModel;
		$this->besturenModel = $besturenModel;
		$this->boekExemplaarModel = $boekExemplaarModel;
		$this->boekRecensieModel = $boekRecensieModel;
		$this->civiBestellingModel = $civiBestellingModel;
		$this->commissieVoorkeurModel = $commissieVoorkeurModel;
		$this->commissiesModel = $commissiesModel;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->corveeVoorkeurenModel = $corveeVoorkeurenModel;
		$this->corveeVrijstellingenModel = $corveeVrijstellingenModel;
		$this->forumPostsModel = $forumPostsModel;
		$this->fotoModel = $fotoModel;
		$this->fotoTagsModel = $fotoTagsModel;
		$this->ketzersModel = $ketzersModel;
		$this->kwalificatiesModel = $kwalificatiesModel;
		$this->lidToestemmingModel = $lidToestemmingModel;
		$this->maaltijdAanmeldingenModel = $maaltijdAanmeldingenModel;
		$this->maaltijdAbonnementenModel = $maaltijdAbonnementenModel;
		$this->onderverenigingenModel = $onderverenigingenModel;
		$this->rechtenGroepenModel = $rechtenGroepenModel;
		$this->voorkeurOpmerkingModel = $voorkeurOpmerkingModel;
		$this->werkgroepenModel = $werkgroepenModel;
		$this->saldoGrafiekModel = $saldoGrafiekModel;
	}

	public function resetPrivateToken($uid) {
		$profiel = $this->profielModel->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}
		$this->accountModel->resetPrivateToken($profiel->getAccount());
		return $this->profiel($uid);
	}

	public function profiel($uid) {
		if ($uid == null) {
			$uid = LoginModel::getUid();
		}

		$profiel = $this->profielModel->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		$fotos = [];
		foreach ($this->fotoTagsModel->find('keyword = ?', [$uid], null, null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = $this->fotoModel->retrieveByUUID($tag->refuuid);
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
			'corveetaken' => $this->corveeTakenModel->getTakenVoorLid($uid),
			'corveevoorkeuren' => $this->corveeVoorkeurenModel->getVoorkeurenVoorLid($uid),
			'corveevrijstelling' => $this->corveeVrijstellingenModel->getVrijstelling($uid),
			'corveekwalificaties' => $this->kwalificatiesModel->getKwalificatiesVanLid($uid),
			'forumpostcount' => $this->forumPostsModel->getAantalForumPostsVoorLid($uid),
			'forumrecent' => $this->forumPostsModel->getRecenteForumPostsVanLid($uid, (int)lid_instelling('forum', 'draden_per_pagina')),
			'boeken' => $this->boekExemplaarModel->getEigendom($uid),
			'recenteAanmeldingen' => $this->maaltijdAanmeldingenModel->getRecenteAanmeldingenVoorLid($uid, strtotime(instelling('maaltijden', 'recent_lidprofiel'))),
			'abos' => $this->maaltijdAbonnementenModel->getAbonnementenVoorLid($uid),
			'gerecenseerdeboeken' => $this->boekRecensieModel->getVoorLid($uid),
			'fotos' => $fotos
		]);
	}

	public function nieuw($lidjaar, $status) {
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) OR !in_array($lidstatus, LidStatus::getTypeOptions())) {
			throw new CsrToegangException();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet AND !LoginModel::mag(P_LEDEN_MOD)) {
			throw new CsrToegangException();
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = $this->profielModel->nieuw((int)$lidjaar, $lidstatus);

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
				$nieuw = !$this->profielModel->exists($profiel);
				$changeEntry = ProfielModel::changelog($diff, LoginModel::getUid());
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						array_push($changeEntry->entries, ...$this->profielModel->wijzig_lidstatus($profiel, $change->old_value));
					}
				}
				$profiel->changelog[] = $changeEntry;
				if ($nieuw) {
					try {
						Database::transaction(function () use ($profiel) {
							$this->profielModel->create($profiel);

							if (filter_input(INPUT_POST, 'toestemming_geven') === 'true') {
								// Sla toesteming op.
								$toestemmingForm = new ToestemmingModalForm(true);
								if ($toestemmingForm->validate()) {
									$this->lidToestemmingModel->save($profiel->uid);
								} else {
									throw new CsrException('Opslaan van toestemming mislukt');
								}
							}
						});
					} catch (CsrException $ex) {
						setMelding($ex->getMessage(), -1);
					}

					setMelding('Profiel succesvol opgeslagen met lidnummer: ' . $profiel->uid, 1);
				} elseif (1 === $this->profielModel->update($profiel)) {
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
		$profiel = $this->profielModel->get($uid);

		if ($profiel === false) {
			throw new CsrNotFoundException();
		}

		return $this->profielBewerken($profiel);
	}

	public function voorkeuren($uid) {
		$profiel = $this->profielModel->get($uid);

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
			foreach ($voorkeuren as $voorkeur) {
				$this->commissieVoorkeurModel->updateOrCreate($voorkeur);
			}
			$this->voorkeurOpmerkingModel->updateOrCreate($opmerking);
			setMelding('Voorkeuren opgeslagen', 1);
			$this->redirectToRoute('profiel-voorkeuren');

		}
		return view('default', ['content' => $form]);
	}

	public function addToGoogleContacts($uid) {
		$profiel = $this->profielModel->get($uid);

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
			'profiel' => ProfielModel::get($uid) ?? LoginModel::getProfiel(),
		]);
	}

	public function verjaardagen() {
		$nu = time();
		return view('verjaardagen.alle', [
			'dezemaand' => date('n', $nu),
			'dezedag' => date('d', $nu),
			'verjaardagen' => VerjaardagenModel::getJaar(),
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
		$profiel = ProfielModel::get($uid);

		if (!$profiel) {
			throw new CsrNotFoundException();
		}

		return new VcardResponse(view('profiel.vcard', [
			'profiel' => $profiel,
		])->toString());
	}

	public function kaartje($uid) {
		return view('profiel.kaartje', ['profiel' => ProfielModel::get($uid)]);
	}

	public function redirectWithUid($route) {
		return $this->redirectToRoute($route, ['uid' => LoginModel::getUid()]);
	}
}
