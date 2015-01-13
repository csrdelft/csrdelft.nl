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
				'nieuw'					 => 'P_LEDEN_MOD,groep:NovCie',
				'lijst'					 => 'P_OUDLEDEN_READ',
				'stamboom'				 => 'P_OUDLEDEN_READ',
				'verjaardagen'			 => 'P_LEDEN_READ',
				'memory'				 => 'P_OUDLEDEN_READ'
			);
		} else {
			$this->acl = array(
				// Profiel
				'bewerken'	 => 'P_PROFIEL_EDIT',
				'voorkeuren' => 'P_PROFIEL_EDIT',
				// Leden
				'nieuw'		 => 'P_LEDEN_MOD'
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
			if (!ProfielModel::existsUid($uid)) {
				setMelding('Dit profiel bestaat niet', -1);
				redirect('/ledenlijst');
			}
			$args = $this->getParams(4);
			array_unshift($args, ProfielModel::get($uid));
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

	public function bewerken(Profiel $profiel) {
		if (!$profiel->magBewerken()) {
			$this->geentoegang();
		}
		$form = new ProfielForm($profiel);
		if ($form->validate()) {

			//duck-pasfoto opslaan
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
				$changelog = $form->changelog($diff);

				//lidstatus wijzigen
				foreach ($diff as $change) {
					if ($change->property === 'status') {
						$changelog .= $this->model->wijzig_lidstatus($profiel, $change->old_value);
					}
				}

				$profiel->changelog = $changelog . $profiel->changelog;

				if ($this->model->update($profiel)) {
					setMelding(count($diff) . ' wijzigingen succesvol opgeslagen', 1);
				} else {
					setMelding('Opslaan van ' . count($diff) . ' wijzigingen mislukt', -1);
				}
			}
			redirect(CSR_ROOT . '/profiel/' . $profiel->uid);
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
			foreach ($form->getValues() as $fieldname => $value) {
				if ($fieldname == 'lidOpmerking') {
					$model->setLidOpmerking($value);
				} else {
					$model->setCommissieVoorkeur(substr($fieldname, 4), $value);
				}
			}
			setMelding('Voorkeuren opgeslagen', 1);
		}
		return $form;
	}

	public function addToGoogleContacts(Profiel $profiel) {
		require_once 'googlesync.class.php';
		GoogleSync::doRequestToken(CSR_ROOT . '/profiel/' . $profiel->uid . '/addToGoogleContacts');
		$gSync = GoogleSync::instance();
		$msg = $gSync->syncLid($profiel->uid);
		setMelding('Opgeslagen in Google Contacts: ' . $msg, 2);
		return $this->profiel($profiel);
	}

	/**
	 * Even wat uitleg over het toevoegen van nieuwe leden:
	 * Door naar de url http://csrdelft.nl/leden/nieuw/2005/noviet/ te gaan wordt er een
	 * nieuw profiel aangemaakt met het opgegeven lidjaar en lidstatus. Vervolgens wordt de browser meteen naar het
	 * bewerken van het nieuwe profiel gestuurd, waar de gegevens van de noviet ingevoerd kunnen
	 * worden. De code daarvoor is gelijk aan die van het bewerken van een bestaand profiel, met
	 * een ander tekstje erboven.
	 * 
	 * @author Hans van Kranenburg (sep 2005)
	 * 
	 */
	public function nieuw($lidjaar, $lidstatus) {
		// Maak van een standaard statusstring van de input
		$lidstatus = 'S_' . strtoupper($lidstatus);
		if (!preg_match('/^[0-9]{4}$/', $lidjaar) OR ! in_array($lidstatus, LidStatus::getTypeOptions())) {
			$this->geentoegang();
		}
		// NovCie mag novieten aanmaken
		if ($lidstatus !== LidStatus::Noviet AND ! LoginModel::mag('P_LEDEN_MOD')) {
			$this->geentoegang();
		}
		try {
			//maak het nieuwe uid aan.
			$uid = ProfielModel::nieuwProfiel($lidjaar, $lidstatus);
			redirect('/profiel/' . $uid . '/bewerken');
		} catch (Exception $e) {
			setMelding('<h3>Nieuw lidnummer aanmaken mislukt.</h3>' . $e->getMessage(), -1);
			redirect('/profiel/');
		}
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
		require_once 'view/LedenMemoryView.class.php';
		$this->view = new LedenMemoryView();
		$this->view->addCompressedResources('ledenmemory');
	}

}
