<?php

require_once 'view/ProfielView.class.php';

/**
 * ProfielController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor de ledenlijst.
 */
class ProfielController extends AclController {

	public function __construct($query) {
		parent::__construct($query, ProfielModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				// Profiel
				'profiel'				 => 'P_OUDLEDEN_READ',
				'bewerken'				 => 'P_PROFIEL_EDIT',
				'voorkeuren'			 => 'P_PROFIEL_EDIT',
				'resetPrivateToken'		 => 'P_PROFIEL_EDIT',
				'addToGoogleContacts'	 => 'P_LEDEN_READ',
				// Leden
				'nieuw'					 => 'P_LEDEN_MOD,commissie:NovCie',
				'lijst'					 => 'P_OUDLEDEN_READ',
				'stamboom'				 => 'P_OUDLEDEN_READ',
				'verjaardagen'			 => 'P_LEDEN_READ',
				'memory'				 => 'P_OUDLEDEN_READ'
			);
		} else {
			$this->acl = array(
				// Profiel
				'bewerken'		 => 'P_PROFIEL_EDIT',
				'voorkeuren'	 => 'P_PROFIEL_EDIT',
				// Leden
				'nieuw'			 => 'P_LEDEN_MOD,commissie:NovCie',
				'memoryscore'	 => 'P_LEDEN_READ',
				'memoryscores'	 => 'P_LEDEN_READ'
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
			$body = parent::performAction($args);
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('profiel');
			$this->view->addCompressedResources('grafiek');
		}
		// Leden
		else {
			$this->action = 'lijst';
			if ($this->hasParam(2)) {
				$this->action = $this->getParam(2);
			}
			if (startsWith($this->action, 'memory')) {
				require_once 'model/LedenMemoryScoresModel.class.php';
				require_once 'view/LedenMemoryView.class.php';
			}
			return parent::performAction($this->getParams(3));
		}
	}

	public function profiel(Profiel $profiel) {
		return new ProfielView($profiel);
	}

	public function resetPrivateToken(Profiel $profiel) {
		AccountModel::instance()->resetPrivateToken($profiel->getAccount());
		return $this->profiel($profiel);
	}

	public function nieuw($lidjaar, $status) {
		// Controleer invoer
		$lidstatus = 'S_' . strtoupper($status);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) OR ! in_array($lidstatus, LidStatus::getTypeOptions())) {
			$this->geentoegang();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet AND ! LoginModel::mag('P_LEDEN_MOD')) {
			$this->geentoegang();
		}
		// Maak nieuw profiel zonder op te slaan
		$profiel = ProfielModel::instance()->nieuw((int) $lidjaar, $lidstatus);
		return $this->bewerken($profiel);
	}

	public function bewerken(Profiel $profiel) {
		if (!$profiel->magBewerken()) {
			$this->geentoegang();
		}
		$form = new ProfielForm($profiel);
		if ($form->validate()) {

			// Duck-pasfoto opslaan
			$duckfoto = $form->findByName('duckfoto');
			if ($duckfoto AND $duckfoto->getModel() instanceof Afbeelding) {
				$filename = $duckfoto->getModel()->filename;
				if ($filename !== 'eend.jpg') {
					$ext = '.' . pathinfo($filename, PATHINFO_EXTENSION);
					$duckfoto->opslaan(PICS_PATH . 'pasfoto/Duckstad/', $profiel->uid . $ext, true);
				}
			}

			$diff = $form->diff();
			if (empty($diff)) {
				setMelding('Geen wijzigingen', 0);
			} else {
				$nieuw = !$this->model->exists($profiel);
				$changelog = $form->changelog($diff, $nieuw);

				// LidStatus wijzigen
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						$changelog .= '[div]' . $this->model->wijzig_lidstatus($profiel, $change->old_value) . '[/div][hr]';
					}
				}

				$profiel->changelog = $changelog . $profiel->changelog;

				if ($nieuw) {
					$this->model->create($profiel);
					setMelding('Profiel succesvol opgeslagen met lidnummer: ' . $profiel->uid, 1);
				} elseif (1 === $this->model->update($profiel)) {
					setMelding(count($diff) . ' wijziging(en) succesvol opgeslagen', 1);
				} else {
					setMelding('Opslaan van ' . count($diff) . ' wijziging(en) mislukt', -1);
				}
			}
			redirect('/profiel/' . $profiel->uid);
		}
		return $form;
	}

	public function voorkeuren(Profiel $profiel) {
		if (!$profiel->magBewerken()) {
			$this->geentoegang();
		}
		require_once 'model/CommissieVoorkeurenModel.class.php';
		require_once 'view/CommissieVoorkeurenView.class.php';
		$form = new CommissieVoorkeurenForm($profiel);
		if ($form->validate()) {
			$model = new CommissieVoorkeurenModel($profiel->uid);
			foreach ($form->getValues() as $attr => $value) {
				if ($attr == 'lidOpmerking') {
					$model->setLidOpmerking($value);
				} else {
					$model->setCommissieVoorkeur(substr($attr, 4), $value);
				}
			}
			setMelding('Voorkeuren opgeslagen', 1);
		}
		return $form;
	}

	public function addToGoogleContacts(Profiel $profiel) {
		try {
			require_once 'googlesync.class.php';
			GoogleSync::doRequestToken(CSR_ROOT . "/profiel/" . $profiel->uid . "/addToGoogleContacts");
			$gSync = GoogleSync::instance();
			$msg = $gSync->syncLid($profiel);
			setMelding('Opgeslagen in Google Contacts: ' . $msg, 1);
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
		}

		redirect(CSR_ROOT . '/profiel/'. $profiel->uid);
	}

	public function lijst() {
		redirect('/ledenlijst');
	}

	public function stamboom($uid = null) {
		require_once 'view/StamboomView.class.php';
		$body = new StamboomView($uid);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('stamboom');
	}

	public function verjaardagen() {
		require_once 'view/VerjaardagenView.class.php';
		$body = new VerjaardagenView('alleverjaardagen');
		$this->view = new CsrLayoutPage($body);
	}

	public function memory() {
		$this->view = new LedenMemoryView();
		$this->view->addCompressedResources('ledenmemory');
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
					$groep = VerticalenModel::instance()->getUUID($groep);
					break;

				case 'lichting.csrdelft.nl':
					$groep = LichtingenModel::get($parts[0]);
					break;
			}
		}
		if ($groep) {
			$data = LedenMemoryScoresModel::instance()->getScores($groep);
		} else {
			$data = LedenMemoryScoresModel::instance()->getAllScores();
		}
		$this->view = new LedenMemoryScoreResponse($data);
	}

}
