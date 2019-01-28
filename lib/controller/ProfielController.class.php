<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\GoogleSync;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\Afbeelding;
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
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\OnderverenigingenModel;
use CsrDelft\model\groepen\RechtenGroepenModel;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\groepen\WerkgroepenModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\LidToestemmingModel;
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
use CsrDelft\view\AlleVerjaardagenView;
use CsrDelft\view\commissievoorkeuren\CommissieVoorkeurenForm;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\fotoalbum\FotoBBView;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\ledenmemory\LedenMemoryScoreForm;
use CsrDelft\view\ledenmemory\LedenMemoryScoreResponse;
use CsrDelft\view\ledenmemory\LedenMemoryView;
use CsrDelft\view\profiel\ProfielForm;
use CsrDelft\view\StamboomView;
use CsrDelft\view\toestemming\ToestemmingModalForm;

/**
 * ProfielController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller voor de ledenlijst.
 *
 * @property ProfielModel $model
 */
class ProfielController extends AclController {
	public function __construct($query) {
		parent::__construct($query, ProfielModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				// Profiel
				'profiel' => 'P_OUDLEDEN_READ',
				'bewerken' => 'P_PROFIEL_EDIT',
				'voorkeuren' => 'P_PROFIEL_EDIT',
				'resetPrivateToken' => 'P_PROFIEL_EDIT',
				'addToGoogleContacts' => 'P_LEDEN_READ',
				// Leden
				'pasfoto' => 'P_OUDLEDEN_READ',
				'nieuw' => 'P_LEDEN_MOD,commissie:NovCie',
				'lijst' => 'P_OUDLEDEN_READ',
				'stamboom' => 'P_OUDLEDEN_READ',
				'verjaardagen' => 'P_LEDEN_READ',
				'memory' => 'P_OUDLEDEN_READ',
			);
		} else {
			$this->acl = array(
				// Profiel
				'bewerken' => 'P_PROFIEL_EDIT',
				'voorkeuren' => 'P_PROFIEL_EDIT',
				// Leden
				'nieuw' => 'P_LEDEN_MOD,commissie:NovCie',
				'memoryscore' => 'P_LEDEN_READ',
				'memoryscores' => 'P_LEDEN_READ',
				'saldo' => 'P_LEDEN_READ',
			);
		}
	}

	public function performAction(array $args = array()) {
		// Profiel
		if ($this->hasParam(1) AND $this->getParam(1) === 'profiel') {
			if ($this->hasParam(2)) {
				$uid = $this->getParam(2);
			} else {
				$uid = LoginModel::getUid();
			}
			if ($this->hasParam(3)) {
				$this->action = $this->getParam(3);
			} else {
				$this->action = 'profiel';
			}
			if ($this->action === 'nieuw' AND $this->hasParam(2)) {
				$args = $this->getParams(4); // status
				array_unshift($args, $uid); // lidjaar
			} elseif (ProfielModel::existsUid($uid)) {
				$args = $this->getParams(4);
				array_unshift($args, ProfielModel::get($uid));
			} else {
				setMelding('Dit profiel bestaat niet', -1);
				redirect('/ledenlijst');
			}
			$this->view = parent::performAction($args);
		}
		else if ($this->hasParam(2) AND $this->getParam(2) === 'pasfoto') {
			$this->action = 'pasfoto';
			parent::performAction([implode('/', $this->getParams(3))]);
		}
		// Leden
		else {
			$this->action = 'lijst';
			if ($this->hasParam(2)) {
				$this->action = $this->getParam(2);
			}
			parent::performAction($this->getParams(3));
		}
	}

	public function profiel(Profiel $profiel) {
		$uid = $profiel->uid;

		$fotos = [];
		foreach (FotoTagsModel::instance()->find('keyword = ?', [$uid], null, null, 3) as $tag) {
			/** @var Foto $foto */
			$foto = FotoModel::instance()->retrieveByUUID($tag->refuuid);
			if ($foto) {
				$fotos[] = new FotoBBView($foto);
			}
		}

		return view('profiel.profiel', [
			'profiel' => $profiel,
			'besturen' => BesturenModel::instance()->getGroepenVoorLid($uid),
			'commissies' => CommissiesModel::instance()->getGroepenVoorLid($uid),
			'werkgroepen' => WerkgroepenModel::instance()->getGroepenVoorLid($uid),
			'onderverenigingen' => OnderverenigingenModel::instance()->getGroepenVoorLid($uid),
			'groepen' => RechtenGroepenModel::instance()->getGroepenVoorLid($uid),
			'ketzers' => KetzersModel::instance()->getGroepenVoorLid($uid),
			'activiteiten' => ActiviteitenModel::instance()->getGroepenVoorLid($uid),
			'bestellinglog' => CiviBestellingModel::instance()->getBeschrijving(CiviBestellingModel::instance()->getBestellingenVoorLid($uid, 10)->fetchAll()),
			'bestellingenlink' => '/fiscaat/bestellingen' . (LoginModel::getUid() === $uid ? '' : '/' . $uid),
			'corveetaken' => CorveeTakenModel::instance()->getTakenVoorLid($uid),
			'corveevoorkeuren' => CorveeVoorkeurenModel::instance()->getVoorkeurenVoorLid($uid),
			'corveevrijstelling' => CorveeVrijstellingenModel::instance()->getVrijstelling($uid),
			'corveekwalificaties' => KwalificatiesModel::instance()->getKwalificatiesVanLid($uid),
			'forumpostcount' => ForumPostsModel::instance()->getAantalForumPostsVoorLid($uid),
			'forumrecent' => ForumPostsModel::instance()->getRecenteForumPostsVanLid($uid, (int)LidInstellingenModel::get('forum', 'draden_per_pagina')),
			'boeken' => BoekExemplaarModel::getEigendom($uid),
			'recenteAanmeldingen' => MaaltijdAanmeldingenModel::instance()->getRecenteAanmeldingenVoorLid($uid, strtotime(InstellingenModel::get('maaltijden', 'recent_lidprofiel'))),
			'abos' => MaaltijdAbonnementenModel::instance()->getAbonnementenVoorLid($uid),
			'gerecenseerdeboeken' => BoekRecensieModel::getVoorLid($uid),
			'fotos' => $fotos
		]);
	}

	public function resetPrivateToken(Profiel $profiel) {
		AccountModel::instance()->resetPrivateToken($profiel->getAccount());
		return $this->profiel($profiel);
	}

	public function nieuw($lidjaar, $status) {
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) OR !in_array($lidstatus, LidStatus::getTypeOptions())) {
			$this->exit_http(403);
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet AND !LoginModel::mag('P_LEDEN_MOD')) {
			$this->exit_http(403);
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = ProfielModel::instance()->nieuw((int)$lidjaar, $lidstatus);
		return $this->bewerken($profiel);
	}

	public function bewerken(Profiel $profiel) {
		if (!$profiel->magBewerken()) {
			$this->exit_http(403);
		}
		$form = new ProfielForm($profiel);
		if ($form->validate()) {
			$diff = $form->diff();
			if (empty($diff)) {
				setMelding('Geen wijzigingen', 0);
			} else {
				$nieuw = !$this->model->exists($profiel);
				$changeEntry = ProfielModel::changelog($diff, LoginModel::getUid());
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						array_push($changeEntry->entries, ...$this->model->wijzig_lidstatus($profiel, $change->old_value));
					}
				}
				$profiel->changelog[] = $changeEntry;
				if ($nieuw) {
					try {
						Database::transaction(function () use ($profiel) {
							$this->model->create($profiel);

							if (filter_input(INPUT_POST, 'toestemming_geven') === 'true') {
								// Sla toesteming op.
								$toestemmingForm = new ToestemmingModalForm(true);
								if ($toestemmingForm->validate()) {
									LidToestemmingModel::instance()->save($profiel->uid);
								} else {
									throw new CsrException('Opslaan van toestemming mislukt');
								}
							}
						});
					} /** @noinspection PhpRedundantCatchClauseInspection */ catch (CsrException $ex) {
						setMelding($ex->getMessage(), -1);
					}

					setMelding('Profiel succesvol opgeslagen met lidnummer: ' . $profiel->uid, 1);
				} elseif (1 === $this->model->update($profiel)) {
					setMelding(count($diff) . ' wijziging(en) succesvol opgeslagen', 1);
				} else {
					setMelding('Opslaan van ' . count($diff) . ' wijziging(en) mislukt', -1);
				}
			}
			redirect('/profiel/' . $profiel->uid);
		}
		return new CsrLayoutPage($form);
	}

	public function voorkeuren(Profiel $profiel) {
		if (!$profiel->magBewerken()) {
			$this->exit_http(403);
		}
		$form = new CommissieVoorkeurenForm($profiel);
		if ($form->isPosted() && $form->validate()) {
			$voorkeuren = $form->getVoorkeuren();
			$opmerking = $form->getOpmerking();
			foreach ($voorkeuren as $voorkeur) {
				CommissieVoorkeurModel::instance()->updateOrCreate($voorkeur);
			}
			VoorkeurOpmerkingModel::instance()->updateOrCreate($opmerking);
			setMelding('Voorkeuren opgeslagen', 1);
			redirect();
		}
		return new CsrLayoutPage($form);
	}

	public function addToGoogleContacts(Profiel $profiel) {
		try {
			GoogleSync::doRequestToken(CSR_ROOT . "/profiel/" . $profiel->uid . "/addToGoogleContacts");
			$gSync = GoogleSync::instance();
			$msg = $gSync->syncLid($profiel);
			setMelding('Opgeslagen in Google Contacts: ' . $msg, 1);
		} catch (CsrException $e) {
			setMelding("Opslaan in Google Contacts mislukt: " . $e->getMessage(), -1);
		}
		redirect(CSR_ROOT . '/profiel/' . $profiel->uid);
	}

	public function lijst() {
		redirect('/ledenlijst');
	}

	public function stamboom($uid = null) {
		$body = new StamboomView($uid);
		$this->view = new CsrLayoutPage($body);
	}

	public function verjaardagen() {
		$body = new AlleVerjaardagenView(VerjaardagenModel::getJaar());
		$this->view = new CsrLayoutPage($body);
	}

	public function saldo($uid, $timespan) {
		if (SaldoGrafiekModel::magGrafiekZien($uid)) {
			$data = SaldoGrafiekModel::getDataPoints($uid, $timespan);
			$this->view = new JsonResponse($data);
		} else {
			$this->exit_http(403);
		}
	}

	public function memory() {
		$this->view = new LedenMemoryView();
	}

	public function memoryscore() {
		$score = LedenMemoryScoresModel::instance()->nieuw();
		$form = new LedenMemoryScoreForm($score);
		if ($form->validate()) {
			LedenMemoryScoresModel::instance()->create($score);
		}
		$this->view = new JsonResponse($score);
	}

	public function memoryscores($groep = null) {
		$parts = explode('@', $groep);
		if (isset($parts[0], $parts[1])) {
			switch ($parts[1]) {
				case 'verticale.csrdelft.nl':
					$groep = VerticalenModel::instance()->retrieveByUUID($groep);
					break;
				case 'lichting.csrdelft.nl':
					$groep = LichtingenModel::get($parts[0]);
					break;
			}
		}
		if ($groep) {
			$data = LedenMemoryScoresModel::instance()->getGroepTopScores($groep);
		} else {
			$data = LedenMemoryScoresModel::instance()->getAllTopScores();
		}
		$this->view = new LedenMemoryScoreResponse($data);
	}

	public function pasfoto($path) {
		try {
			$image = new Afbeelding(safe_combine_path(PASFOTO_PATH, $path));
			$image->serve();
		} catch (CsrGebruikerException $ex) {
			redirect("/plaetjes/geen-foto.jpg");
		}
	}
}
