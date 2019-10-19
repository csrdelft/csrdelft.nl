<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\GoogleSync;
use CsrDelft\controller\framework\QueryParamTrait;
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
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ProfielController {
	private $model;

	public function __construct() {
		$this->model = ProfielModel::instance();
	}

	public function resetPrivateToken($uid) {
		$profiel = ProfielModel::instance()->get($uid);

		if ($profiel === false) {
			throw new ResourceNotFoundException();
		}
		AccountModel::instance()->resetPrivateToken($profiel->getAccount());
		return $this->profiel($uid);
	}

	public function profiel($uid) {
		if ($uid == null) {
			$uid = LoginModel::getUid();
		}

		$profiel = ProfielModel::instance()->get($uid);

		if ($profiel === false) {
			throw new ResourceNotFoundException();
		}

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
			'forumrecent' => ForumPostsModel::instance()->getRecenteForumPostsVanLid($uid, (int)lid_instelling('forum', 'draden_per_pagina')),
			'boeken' => BoekExemplaarModel::instance()->getEigendom($uid),
			'recenteAanmeldingen' => MaaltijdAanmeldingenModel::instance()->getRecenteAanmeldingenVoorLid($uid, strtotime(instelling('maaltijden', 'recent_lidprofiel'))),
			'abos' => MaaltijdAbonnementenModel::instance()->getAbonnementenVoorLid($uid),
			'gerecenseerdeboeken' => BoekRecensieModel::instance()->getVoorLid($uid),
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
		$profiel = ProfielModel::instance()->nieuw((int)$lidjaar, $lidstatus);

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
		if ($alleenFormulier) {
			return view('plain', ['titel' => 'Noviet toevoegen', 'content' => $form]);
		}
		return view('default', ['content' => $form]);
	}

	public function bewerken($uid) {
		$profiel = ProfielModel::instance()->get($uid);

		if ($profiel === false) {
			throw new ResourceNotFoundException();
		}

		return $this->profielBewerken($profiel);
	}

	public function voorkeuren($uid) {
		$profiel = ProfielModel::instance()->get($uid);

		if ($profiel === false) {
			throw new ResourceNotFoundException();
		}
		if (!$profiel->magBewerken()) {
			throw new CsrToegangException();
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
		return view('default', ['content' => $form]);
	}

	public function addToGoogleContacts($uid) {
		$profiel = ProfielModel::instance()->get($uid);

		if ($profiel === false) {
			throw new ResourceNotFoundException();
		}
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
		if (SaldoGrafiekModel::magGrafiekZien($uid)) {
			$data = SaldoGrafiekModel::getDataPoints($uid, $timespan);
			return new JsonResponse($data);
		} else {
			throw new CsrToegangException();
		}
	}

	public function pasfoto($uid, $vorm = 'civitas') {
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			redirect('/images/geen-foto.jpg');
		}
		if (!is_zichtbaar($profiel, 'profielfoto', 'intern')) {
			redirect('/images/geen-foto.jpg');
		}
		$path = $profiel->getPasfotoInternalPath(false, $vorm);
		if ($path === null) {
			redirect('/images/geen-foto.jpg');
		}
		$image = new Afbeelding($path);
		return new BinaryFileResponse($image->getFullPath());
	}

	public function vcard($uid) {
		$profiel = ProfielModel::get($uid);

		if (!$profiel) {
			throw new ResourceNotFoundException();
		}

		header('Content-Type: text/x-vcard; charset=UTF-8');
		return crlf_endings(view('profiel.vcard', [
			'profiel' => $profiel,
		]));
	}

	public function kaartje($uid) {
		return view('profiel.kaartje', ['profiel' => ProfielModel::get($uid)]);
	}

	public function redirect($target) {
		$uid = LoginModel::getUid();
		redirect("/profiel/$uid/$target");
	}
}
