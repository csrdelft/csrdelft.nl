<?php

require_once 'groepen.class.php';
require_once 'MVC/controller/Controller.abstract.php';

/**
 * class.groepcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Groepcontroller wordt ge__construct() met één argument, een querystring.
 * Die bestaat uit door slashes gescheiden waarden in de volgende volgorde:
 *
 * <groepId of groepNaam>/[<actie>/[<parameters voor actie>]]
 *
 * bijvoorbeeld voor het verwijderen van een lid uit de PubCie
 *
 * PubCie/verwijderLid/0436
 *
 * Het gaat hierbij om GET-parameters, POST-dingen worden gewoon in de
 * controller uit de POST-array getrokken...
 */
class Groepcontroller extends Controller {

	private $groep;
	protected $valid = true;
	protected $errors = '';

	public function __construct($querystring) {
		parent::__construct($querystring, null);

		//groep-object inladen
		if ($this->hasParam(0)) {
			try {
				$this->groep = new OldGroep($this->getParam(0));
			} catch (Exception $e) {
				SimpleHTML::setMelding($e->getMessage(), -1);
				redirect(CSR_ROOT . '/actueel/groepen/');
			}
			if ($this->groep->getId() == 0 AND isset($_GET['gtype'])) {
				try {
					$groepen = new Groepen($_GET['gtype']);
				} catch (Exception $e) {
					SimpleHTML::setMelding($e->getMessage(), -1);
					redirect(CSR_ROOT . '/actueel/groepen/');
				}
				$this->groep->setGtype($groepen);
				if (!($this->groep->getType() instanceof Groepen)) {
					SimpleHTML::setMelding('Groeptype bestaat niet', -1);
					redirect($this->getUrl());
				}
			}
		}
		$this->action = 'standaard';
		//action voor deze controller goedzetten.
		if ($this->hasParam(1) AND $this->hasAction($this->getParam(1))) {
			$this->action = $this->getParam(1);
		}
		//content-object aanmaken..
		$this->view = new Groepcontent($this->groep);

		//controleer dat we geen lege groep weergeven.
		if ($this->action == 'standaard' AND $this->groep->getId() == 0) {
			SimpleHTML::setMelding('We geven geen 0-groepen weer! (Groepcontroller::__construct())', -1);
			redirect(CSR_ROOT . '/actueel/groepen/');
		}
	}

	public function standaard() {
		$this->view->setAction('view');
	}

	protected function mag($action) {
		// wordt afgehandeld per actie
		return true;
	}

	public function addError($error) {
		$this->valid = false;
		$this->errors.=$error . '<br />';
	}

	public function getUrl($action = null) {
		$url = CSR_ROOT . '/actueel/groepen/' . $this->groep->getType()->getNaam() . '/' . $this->groep->getId() . '/';
		if ($action != null AND $this->hasAction($action)) {
			if ($action != 'standaard') {
				$url.=$action;
			}
		} elseif ($this->action != 'standaard') {
			$url.=$this->action;
		}
		return $url;
	}

	/**
	 * Valideer de formulierinvoer voor een groep.
	 * Beetje gecompliceerd door de verschillende permissielagen, maargoed.
	 */
	public function groepValidator() {
		//Velden beschikbaar voor groepadmins en voor leden die hun groep mogen aanpassen/maken
		if ($this->groep->isAdmin() OR $this->groep->isEigenaar()) {
			//snaam is alleen relevant bij het maken van een nieuwe groep, bij maken opvolger is snaam ook al bekend.
			if (!isset($_SESSION['oudegroep']) AND $this->groep->getSnaam() == '') {
				if ($this->groep->getId() == 0 AND ! isset($_POST['snaam'])) {
					$this->addError("Korte naam is verplicht bij een nieuwe groep.");
				} else {
					if ($this->groep->getId() == 0) {
						if (strlen(trim($_POST['snaam'])) < 3) {
							$this->addError("Korte naam moet minstens drie tekens lang zijn.");
						}
						if (strlen(trim($_POST['snaam'])) > 20) {
							$this->addError("Korte naam mag maximaal 20 tekens bevatten.");
						}
						if (preg_match('/\s/', trim($_POST['snaam']))) {
							$this->addError("Korte naam mag geen spaties bevatten.");
						}
					}
				}
			}

			if (isset($_POST['naam'], $_POST['sbeschrijving'], $_POST['status'], $_POST['begin'], $_POST['einde'], $_POST['toonFuncties'])) {
				if (strlen(trim($_POST['naam'])) < 3) {
					$this->addError("Naam moet minstens drie tekens lang zijn.");
				}
				if (strlen(trim($_POST['sbeschrijving'])) < 5) {
					$this->addError("Korte beschrijving moet minstens vijf tekens lang zijn.");
				}
				if (!preg_match('/\d{4}-\d{2}-\d{2}/', trim($_POST['begin']))) {
					$this->addError("De begindatum is niet geldig. Gebruik JJJJ-mm-dd.");
				}
				if (trim($_POST['begin']) == '0000-00-00') {
					$this->addError("De begindatum mag niet 0000-00-00 zijn.");
				}
				if ($_POST['einde'] != '0000-00-00' AND strtotime($_POST['begin']) > strtotime($_POST['einde'])) {
					$this->addError('Begindatum moet voor de einddatum liggen');
				}
				if (!preg_match('/\d{4}-\d{2}-\d{2}/', trim($_POST['einde']))) {
					$this->addError("De begindatum is niet geldig. Gebruik JJJJ-mm-dd.");
				}
				if (!in_array($_POST['toonFuncties'], array('tonen', 'verbergen', 'niet', 'tonenzonderinvoer'))) {
					$this->addError("ToonFuncties mag deze waarden niet hebben.");
				}
				if (isset($_POST['eigenaar'])) {
					if ($_POST['eigenaar'] != '') {
						if (strlen(trim($_POST['eigenaar'])) > 255) {
							$this->addError("Eigenaar mag maximaal 255 tekens zijn.");
						}
						if (Lid::isValidUid($_POST['eigenaar'])) {
							if (!Lid::exists($_POST['eigenaar'])) {
								$this->addError("Niet bestaande uid voor eigenaar opgegeven.");
							}
						}
					}
				}
				if (!preg_match('/(h|f|o)t/', $_POST['status'])) {
					$this->addError("De status is niet geldig.");
				} else {
					if ($_POST['status'] == 'ot' AND trim($_POST['einde']) == '0000-00-00') {
						$this->addError("Een o.t. groep moet een einddatum bevatten.");
					}

					//Controleren of er geen h.t. groep bestaat met dezelfde snaam.
					if ($this->groep->getId() == 0 AND isset($_POST['snaam'])) {
						$snaam = $_POST['snaam'];
					} else {
						$snaam = null;
					}
					if ($_POST['status'] == 'ht') {
						if ($this->groep->hasHt($snaam)) {
							$this->addError("Er is al een h.t.-groep met deze 'korte naam'. Wijzig status naar o.t. of f.t. (of kies een andere 'korte naam')");
						}
						if (isset($_POST['aanmeldbaar'], $_POST['limiet'])) {
							if ($_POST['limiet'] < 0 OR $_POST['limiet'] > 200) {
								$this->addError("Kies een limiet tussen 0 en 200");
							}
						}
					}
				}
			} else {
				$this->addError("Het formulier is niet compleet.");
			}
		}
		//velden beschikbaar voor groepOps
		if (!isset($_POST['beschrijving'])) {
			$this->addError("Het veld beschrijving mist.");
		}
		return $this->valid;
	}

	/**
	 * Bewerken en opslaan van groepen. Groepen mogen door groepadmins (groeplid.op=='1')
	 * voor een deel bewerkt worden, de P_ADMINS kunnen alles aanpassen. Hier wordt de
	 * toegangscontrole voor verschillende velden geregeld.
	 */
	public function bewerken() {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			SimpleHTML::setMelding('Niet voldoende rechten voor deze actie', -1);
			redirect($this->getUrl('standaard'));
		}
		$this->view->setAction('edit');

		/* Als er een derde argument meegegeven wordt is dat het id van de groep waar 
		 * een opvolger voor gemaakt moet worden. We nemen wat dingen over van die oude groep,
		 * vaak erg handig.
		 */
		if ($this->hasParam(2) AND preg_match('/[0-9]*/', $this->getParam(2))) {
			$oudeGroep = new OldGroep($this->getParam(2));
			if ($oudeGroep instanceof OldGroep) {
				$this->groep->setValue('snaam', $oudeGroep->getSnaam());
				$_SESSION['oudegroep']['snaam'] = $oudeGroep->getSnaam();
				$this->groep->setValue('naam', $oudeGroep->getNaam());
				$this->groep->setValue('sbeschrijving', $oudeGroep->getSbeschrijving());
				$this->groep->setValue('aanmeldbaar', $oudeGroep->getAanmeldbaar());
				$this->groep->setValue('limiet', $oudeGroep->getLimiet());
				$this->groep->setValue('toonFuncties', $oudeGroep->getToonFuncties());
				$this->groep->setValue('toonPasfotos', $oudeGroep->getToonPasfotos());
				$this->groep->setValue('lidIsMod', $oudeGroep->getLidIsMod());
				$this->groep->setFunctiefilter($oudeGroep->getFunctiefilter());
				if (LoginModel::getUid() == $oudeGroep->getEigenaar() OR ! Lid::isValidUid($oudeGroep->getEigenaar())) {
					$this->groep->setValue('eigenaar', $oudeGroep->getEigenaar());
					$_SESSION['oudegroep']['eigenaar'] = $oudeGroep->getEigenaar();
				}
			}
		}

		if ($this->isPosted()) {
			if ($this->groepValidator()) {
				//slaan we een nieuwe groep op?
				if ($this->groep->getId() == 0) {
					if (isset($_SESSION['oudegroep']) AND ! $this->groep->isAdmin()) {
						$this->groep->setValue('snaam', $_SESSION['oudegroep']['snaam']);
					} else {
						$this->groep->setValue('snaam', $_POST['snaam']);
					}
				}

				//velden alleen voor admins of eigenaars van groep
				if ($this->groep->isAdmin() OR $this->groep->isEigenaar()) {
					$this->groep->setValue('naam', $_POST['naam']);
					$this->groep->setValue('sbeschrijving', $_POST['sbeschrijving']);
					$this->groep->setValue('begin', $_POST['begin']);
					$this->groep->setValue('einde', $_POST['einde']);

					//bij sjaarsactie(gtype:11) blijft status ht
					if ($this->groep->isAdmin() OR $this->groep->getType()->getId() != 11) {
						$this->groep->setValue('status', $_POST['status']);
					} else {
						$this->groep->setValue('status', 'ht');
					}

					//ht-groepen kunnen aanmeldbaar gemaakt worden, ot groepen zijn nooit
					//aanmeldbaar
					if ($this->groep->getStatus() == 'ht') {
						if (isset($_POST['aanmeldbaar'])) {
							//bij sjaarsacties(gtype:11) alleen aanmeldbaar voor laatste lichting
							if ($this->groep->getType()->getId() == 11 AND ( $this->groep->getId() == 0 OR ! $this->groep->isAdmin())) {
								$this->groep->setValue('aanmeldbaar', 'lichting:' . Lichting::getJongsteLichting());
							} else {
								$this->groep->setValue('aanmeldbaar', $_POST['aanmeldbaar']);
							}
							$this->groep->setValue('limiet', $_POST['limiet']);
						} else {
							$this->groep->setValue('aanmeldbaar', '');
							$this->groep->setValue('limiet', 0);
						}
					}
					$this->groep->setValue('toonFuncties', $_POST['toonFuncties']);
					$this->groep->setFunctiefilter($_POST['functiefilter']);

					if (isset($_POST['toonPasfotos'])) {
						$this->groep->setValue('toonPasfotos', 1);
					} else {
						$this->groep->setValue('toonPasfotos', 0);
					}
					if (isset($_POST['lidIsMod'])) {
						$this->groep->setValue('lidIsMod', 1);
					} else {
						$this->groep->setValue('lidIsMod', 0);
					}
					if ($this->groep->isAdmin()) {
						$this->groep->setValue('eigenaar', $_POST['eigenaar']);
					} elseif (isset($_SESSION['oudegroep']['eigenaar'])) {
						$this->groep->setValue('eigenaar', $_SESSION['oudegroep']['eigenaar']);
					}
				}
				$this->groep->setValue('beschrijving', $_POST['beschrijving']);

				if ($this->groep->save()) {
					SimpleHTML::setMelding('Opslaan van groep gelukt!', 1);
					if (isset($_SESSION['oudegroep'])) {
						$_SESSION['oudegroep'] = null;
					}
					try {
						$this->groep->save_ldap();
					} catch (Exception $e) {
						//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
						SimpleHTML::setMelding($e->getMessage(), 0);
					}
				} else {
					SimpleHTML::setMelding('Opslaan van groep mislukt. (returned from OldGroep::save() called by Groepcontroller::bewerken())', -1);
				}
				redirect($this->getUrl('standaard'));
			} else {
				//geposte waarden in het object stoppen zodat de template ze zo in het
				//formulier kan knallen
				$fields = array('snaam', 'naam', 'sbeschrijving', 'beschrijving', 'zichtbaar', 'status',
					'begin', 'einde', 'aanmeldbaar', 'limiet', 'toonFuncties', 'toonPasfotos',
					'lidIsMod', 'eigenaar');

				foreach ($fields as $field) {
					if (isset($_POST[$field])) {
						$this->groep->setValue($field, $_POST[$field]);
					}
				}
				if (isset($_POST['functiefilter'])) {
					$this->groep->setFunctiefilter($_POST['functiefilter']);
				}
				//de eventuele fouten van de groepValidator aan de melding toevoegen.
				SimpleHTML::setMelding($this->errors, -1);
			}
		}
	}

	/**
	 * Een groep permanent verwijderen.
	 */
	public function verwijderen() {
		$groeptypenaam = $this->groep->getType()->getNaam();
		if ($this->groep->isAdmin()) {
			if ($this->groep->delete()) {
				SimpleHTML::setMelding('Groep met succes verwijderd.', 1);
				try {
					$this->groep->save_ldap();
				} catch (Exception $e) {
					//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
					SimpleHTML::setMelding($e->getMessage(), 0);
				}
			} else {
				SimpleHTML::setMelding('Groep verwijderen mislukt Groepcontroller::deleteGroep()', -1);
			}
		} else {
			SimpleHTML::setMelding('Niet voldoende rechten voor deze actie', -1);
		}
		redirect(CSR_ROOT . '/actueel/groepen/' . $groeptypenaam);
	}

	/**
	 * Ingelogde leden kunnen zich aanmelden.
	 */
	public function aanmelden() {
		if ($this->groep->magAanmelden()) {
			$functie = '';
			if (isset($_POST['functie'])) {
				$functie = $_POST['functie'];
				if (is_array($functie)) {
					$functie = implode('&&', $functie);
				}
			}
			if ($this->groep->meldAan($functie)) {
				try {
					$this->groep->save_ldap();
				} catch (Exception $e) {
					//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
					SimpleHTML::setMelding($e->getMessage(), 0);
				}
			} else {
				SimpleHTML::setMelding('Aanmelden voor groep mislukt.', -1);
			}
		} else {
			SimpleHTML::setMelding('U kunt zich niet aanmelden voor deze groep, wellicht is hij vol.', 0);
		}
		if ($this->hasParam(2) AND $this->getParam(2) == 'return') {
			$url = HTTP_REFERER . '#groep' . $this->groep->getId();
		} else {
			$url = $this->getUrl('standaard');
		}
		redirect($url);
	}

	/**
	 * Leden toevoegen aan een groep.
	 */
	public function addLid() {
		if (!$this->groep->magBewerken()) {
			SimpleHTML::setMelding('Niet voldoende rechten voor deze actie', -1);
			redirect($this->getUrl('standaard'));
		}
		$this->view->setAction('addLid');
		if (isset($_POST['naam'], $_POST['functie']) AND is_array($_POST['naam']) AND is_array($_POST['functie']) AND count($_POST['naam']) == count($_POST['functie'])) {
			//nieuwe groepleden erin stoppen.
			$success = true;
			$aantal = 0;
			for ($i = 0; $i < count($_POST['naam']); $i++) {
				if (Lid::isValidUid($_POST['naam'][$i])) {
					if (!$this->groep->addLid($_POST['naam'][$i], $_POST['functie'][$i])) {
						//er gaat iets mis, zet $success op false;
						$success = false;
					} else {
						$aantal++;
					}
				}
			}
			if ($success === true) {
				SimpleHTML::setMelding($aantal . ' leden met succes toegevoegd.', 1);
			} else {
				SimpleHTML::setMelding('Niet alle leden met succes toegevoegd. Wellicht waren sommigen al lid van deze groep? (Groepcontroller::addLid())', -1);
			}
			try {
				$this->groep->save_ldap();
			} catch (Exception $e) {
				//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
				SimpleHTML::setMelding($e->getMessage(), 0);
			}
			redirect($this->getUrl('standaard') . '#lidlijst');
		}
	}

	/**
	 * Leden verwijderen uit een groep
	 */
	public function verwijderLid() {
		if ($this->hasParam(2) AND Lid::isValidUid($this->getParam(2)) AND $this->groep->magBewerken()) {
			if ($this->groep->verwijderLid($this->getParam(2))) {
				SimpleHTML::setMelding('Lid is met succes verwijderd uit de groep.', 1);
				try {
					$this->groep->save_ldap();
				} catch (Exception $e) {
					//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
					SimpleHTML::setMelding($e->getMessage(), 0);
				}
			} else {
				SimpleHTML::setMelding('Lid uit groep verwijderen mislukt (GroepController::verwijderLid()).', -1);
			}
			redirect($this->getUrl('standaard') . '#lidlijst');
		}
	}

	/**
	 * Opmerking/functie van een lid aanpassen, return functie of een foutmelding
	 */
	public function bewerkfunctieLid() {
		if (!$this->groep->magBewerken() AND LoginModel::getUid() != $this->getParam(2)) {
			echo '<span class="melding">Onvoldoende rechten voor deze actie</span>';
			exit;
		}
		if ($this->hasParam(2) AND isset($_POST['functie'])) {
			if (Lid::isValidUid($this->getParam(2)) AND $this->groep->isLid($this->getParam(2))) {
				$functie = $_POST['functie'];
				if (is_array($functie)) {
					$functie = implode('&&', $functie);
				}
				if ($this->groep->addLid($this->getParam(2), $functie, $bewerken = true)) {
					try {
						$this->groep->save_ldap();
					} catch (Exception $e) {
						//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
						SimpleHTML::setMelding($e->getMessage(), 0);
					}
					echo $functie;
				} else {
					echo '<span class="melding">Opmerking opslaan mislukt (GroepController::bewerkfunctieLid()).</span>';
				}
			} else {
				echo '<span class="melding">Ongeldig uid of lid niet in groep</span>';
			}
		}
		exit;
	}

	/**
	 * Een lid naar de eerstvolgende o.t. groep verplaatsen.
	 */
	public function maakLidOt() {
		if ($this->hasParam(2) AND Lid::isValidUid($this->getParam(2)) AND $this->groep->magBewerken()) {
			if ($this->groep->maakLidOt($this->getParam(2))) {
				SimpleHTML::setMelding('Lid naar o.t.-groep verplaatsen gelukt.', 1);
				try {
					$this->groep->save_ldap();
				} catch (Exception $e) {
					//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
					SimpleHTML::setMelding($e->getMessage(), 0);
				}
			} else {
				SimpleHTML::setMelding('Lid naar o.t.-groep verplaatsen mislukt. [' . $this->groep->getError() . ']  (GroepController::maakLidOt())', -1);
			}
			redirect($this->getUrl('standaard') . '#lidlijst');
		}
	}

	/**
	 * De groep o.t. maken.
	 */
	public function maakGroepOt() {
		if ($this->groep->isAdmin() OR $this->groep->isEigenaar()) {
			if ($this->groep->getStatus() == 'ht') {
				if ($this->groep->maakOt()) {
					SimpleHTML::setMelding('Groep o.t. maken gelukt.', 1);
					try {
						$this->groep->save_ldap();
					} catch (Exception $e) {
						//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
						SimpleHTML::setMelding($e->getMessage(), 0);
					}
				} else {
					SimpleHTML::setMelding('Groep o.t. maken mislukt [' . $this->groep->getError() . '] (GroepController::maakGroepOt())', -1);
				}
			} else {
				SimpleHTML::setMelding('Groep kan niet o.t. gemaakt worden omdat groep niet h.t. is.', -1);
			}
			redirect($this->getUrl('standaard'));
		}
	}

	public function geschiedenis() {
		$this->view = new Groepgeschiedeniscontent(new Groepen($_GET['gtype']));
	}

	//we willen de volgende acties met javascript initieren, dus niet de hele site-structuur eromheen
	//hebben, daarom sluiten we aan het einde van elke methode af met exit;
	public function lidLijst() {
		$this->view = new GroepledenContent($this->groep);
		$this->view->view();
		exit;
	}

	public function pasfotos() {
		$this->view = new GroepledenContent($this->groep, 'pasfotos');
		$this->view->view();
		exit;
	}

	public function emails() {
		if ($this->groep->isIngelogged()) {
			$this->view = new GroepEmailContent($this->groep);
			$this->view->view();
		}
		exit;
	}

	public function stats() {
		if ($this->groep->isAdmin() OR $this->groep->isOp() OR $this->groep->isEigenaar() OR ( $this->groep->isAanmeldbaar() AND $this->groep->isIngelogged())) {
			$this->view = new GroepStatsContent($this->groep);
			$this->view->view();
		}
		exit;
	}

}

?>
