<?php

require_once 'MVC/controller/Controller.abstract.php';
require_once 'bibliotheek/boek.class.php';
require_once 'bibliotheek/catalogus.class.php';

require_once 'bibliotheek/bibliotheekcontent.class.php';

/**
 * bibliotheekcontroller.class.php	|	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends Controller {

	/** @var BewerkBoek|NieuwBoek */
	public $boek;
	public $baseurl = '/communicatie/bibliotheek/';

	/**
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring) {
		parent::__construct($querystring, null);

		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.

		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		} else {
			$this->action = 'catalogustonen';
		}
		/*
		 * niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
		 * zonder P_BIEB_MOD, en gebruikers met, zodat bij niet bestaande acties
		 * netjes gewoon de catalogus getoond wordt. 
		 */
		//iedereen(ook uitgelogd) mag catalogus bekijken.
		$allow = array('default', 'catalogusdata');
		//met biebrechten mag je meer
		if (LoginModel::mag('P_BIEB_READ')) {
			$allow = array_merge($allow, array('default', 'boek', 'nieuwboek', 'bewerkboek', 'verwijderboek',
				'bewerkbeschrijving', 'verwijderbeschrijving',
				'addexemplaar', 'verwijderexemplaar',
				'exemplaarlenen', 'exemplaarteruggegeven', 'exemplaarterugontvangen', 'exemplaarvermist', 'exemplaargevonden',
				'autocomplete'));
		}
		if (!in_array($this->action, $allow)) {
			$this->action = 'catalogustonen';
		}
	}

	/**
	 * Wordt op diverse plekken geregeld.
	 */
	protected function mag($action) {
		return true;
	}

	/**
	 * Catalogus tonen
	 * 
	 * /[filters]
	 * 
	 */
	protected function catalogustonen() {
		$this->view = new BibliotheekCatalogusContent();
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 */
	protected function catalogusdata() {
		$catalogus = new Catalogus();
		$this->view = new BibliotheekCatalogusDatatableContent($catalogus);
		$this->view->view();
		exit;
	}

	/**
	 * Laad een boek object
	 * 
	 * ga er van uit dat in getParam(1) een boekid staat en laad dat in.
	 * @param $boekid	$boekid
	 * 					of leeg: gebruikt getParam()
	 */
	private function loadBoek($boekid = null) {
		if ($this->hasParam(1) OR $boekid !== null) {
			if ($boekid === null) {
				$boekid = $this->getParam(1);
			}
			if ($this->hasParam(2) AND in_array($this->action, array('bewerkbeschrijving', 'verwijderbeschrijving'))) {
				$beschrijvingsid = (int) $this->getParam(2);
			} else {
				$beschrijvingsid = 0; //nieuwe beschrijving
			}

			try {
				$this->boek = new BewerkBoek($boekid, $beschrijvingsid);
			} catch (Exception $e) {
				invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/', $e->getMessage());
			}
		}
	}

	/**
	 * Boekpagina weergeven
	 * 
	 * /boek/id
	 */
	protected function boek() {
		$this->loadBoek();
		$this->view = new BibliotheekBoekContent($this->boek);
	}

	/**
	 * Verwerken van bewerking van een veld op de boekpagina
	 * 
	 * /bewerkboek/id
	 */
	protected function bewerkboek() {
		$this->loadBoek();
		if (!$this->boek->isEigenaar()) {
			echo json_encode(array('melding' => 'Onvoldoende rechten voor deze actie'));
			exit;
		}
		if (isset($_POST['id'])) {
			try {
				if ($this->boek->validField($_POST['id']) AND $this->boek->saveField($_POST['id'])) {
					$return['value'] = $this->boek->getProperty($_POST['id']) . '';
					$return['melding'] = 'Opgeslagen';
				} else {
					$return['melding'] = 'Fout: ' . $this->boek->getField($_POST['id'])->getError() . ' ' . $this->boek->getError();
				}
			} catch (Exception $e) {
				$return['melding'] = 'Fout: ' . $e->getMessage();
			}
		} else {
			$return['melding'] = '$_POST["id"] is leeg!';
		}
		echo json_encode($return);
		exit;
	}

	/**
	 * Nieuw boek aanmaken, met formulier
	 * 
	 * /nieuwboek
	 * /boek[/0]
	 * 
	 */
	protected function nieuwboek() {
		//leeg object Boek laden
		$this->boek = new NieuwBoek();
		//Eerst ongewensten de deur wijzen
		if (!$this->boek->magBekijken()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/', 'Onvoldoende rechten voor deze actie. Biebcontrllr::addboek');
		}
		//formulier verwerken, als het onvoldoende is terug naar formulier
		if ($this->boek->validFormulier() AND $this->boek->saveFormulier()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId());
		} else {
			$this->view = new BibliotheekBoekContent($this->boek);
		}
	}

	/**
	 * Verwijder boek
	 * 
	 * /verwijderboek/id
	 */
	protected function verwijderboek() {
		$this->loadBoek();
		if (!$this->boek->magVerwijderen()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/', 'Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving');
		}
		if ($this->boek->delete()) {
			$melding = array('Boek met succes verwijderd.', 1);
		} else {
			$melding = 'Boek verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderboek()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/', $melding);
	}

	/**
	 * Boekbeschrijving aanpassen
	 * 
	 * /bewerkbeschrijving/id/beschrijvingsid
	 */
	protected function bewerkbeschrijving() {
		$this->loadBoek();
		if ($this->boek->getEditBeschrijving()->getId() != 0 AND ! $this->boek->magBeschrijvingVerwijderen()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), 'Onvoldoende rechten voor deze actie. Biebcontrllr::bewerkbeschrijving()');
		}

		//controleer en sla op of geef de bewerkvelden met eventuele foutmeldingen
		if ($this->boek->validFormulier() AND $this->boek->saveFormulier()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId() . '#beschrijving' . $this->boek->getEditBeschrijving()->getId());
		} else {
			$this->view = new BibliotheekBoekContent($this->boek);
		}
	}

	/**
	 * Boekbeschrijving verwijderen
	 * 
	 * /verwijderbeschrijving/id/beschrijvingsid
	 */
	protected function verwijderbeschrijving() {
		$this->loadBoek();

		if (!$this->boek->magBeschrijvingVerwijderen()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), 'Onvoldoende rechten voor deze actie. Biebcontrllr::verwijderbeschrijving()');
		}
		if ($this->boek->verwijderBeschrijving()) {
			$melding = array('Beschrijving met succes verwijderd.', 1);
		} else {
			$melding = 'Beschrijving verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderbeschrijving()';
		}

		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar toevoegen
	 * /addexemplaar/$boekid[/$eigenaarid]
	 */
	protected function addexemplaar() {
		$this->loadBoek();
		if (!$this->boek->magBekijken()) {
			invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), 'Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()');
		}

		if ($this->hasParam(2)) {
			$eigenaar = $this->getParam(2);
		} else {
			$eigenaar = LoginModel::getUid();
		}
		if (Lid::isValidUid($eigenaar)) {
			if ($this->boek->addExemplaar($eigenaar)) {
				$melding = array('Exemplaar met succes toegevoegd.', 1);
			} else {
				$melding = 'Exemplaar toevoegen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::addexemplaar()';
			}
		} else {
			$melding = 'Ongeldig uid "' . $eigenaar . '" Biebcontrllr::addexemplaar()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$boekid/$exemplaarid
	 */
	protected function verwijderexemplaar() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))) {
			if ($this->boek->verwijderExemplaar($this->getParam(2))) {
				$melding = array('Exemplaar met succes verwijderd.', 1);
			} else {
				$melding = 'Exemplaar verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderexemplaar()';
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. Biebcontrllr::verwijderexemplaar()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar is geleend of wordt uitgeleend door eigenaar
	 * kan door iedereen, inclusief eigenaar
	 * 
	 * /exemplaarlenen/id/exemplaarid[/ander]
	 */
	protected function exemplaarlenen() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->magBekijken()) {
			//een exemplaar wordt door eigenaar uitgeleend
			if ($this->hasParam(3) AND $this->getParam(3) == 'ander') {
				if ($this->boek->isEigenaar($this->getParam(2))) {
					if (isset($_POST['id'])) {
						if ($this->boek->validField($_POST['id']) AND $this->boek->saveField($_POST['id'])) {
							$melding = array('Exemplaar uitgeleend', 1);
						} else {
							$melding = 'Exemplaar uitlenen is mislukt. ' . $this->boek->getField($_POST['id'])->getError() . '- ' . $this->boek->getError() . 'Biebcontrllr::exemplaarlenen()';
						}
					} else {
						$melding = '$_POST[id] is leeg';
					}
				} else {
					$melding = 'U moet eigenaar zijn voor deze actie. Biebcontrllr::exemplaarlenen()';
				}
				// iemand leent een exemplaar
			} else {
				if ($this->boek->leenExemplaar($this->getParam(2))) {
					$melding = array('Exemplaar geleend.', 1);
				} else {
					$melding = 'Exemplaar lenen is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarlenen()';
				}
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarlenen()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId() . '#exemplaren', $melding);
	}

	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 * 
	 * /exemplaarteruggegeven/id/exemplaarid
	 */
	protected function exemplaarteruggegeven() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->isLener($this->getParam(2))) {
			if ($this->boek->teruggevenExemplaar($this->getParam(2))) {
				$melding = array('Exemplaar is teruggegeven.', 1);
			} else {
				$melding = 'Teruggave van exemplaar melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarteruggegeven()';
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. ' . $this->boek->getError() . ' Biebcontrllr::exemplaarteruggegeven()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 * 
	 * /exemplaarterugontvangen/id/exemplaarid
	 */
	protected function exemplaarterugontvangen() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))) {
			if ($this->boek->terugontvangenExemplaar($this->getParam(2))) {
				$melding = array('Exemplaar terugontvangen.', 1);
			} else {
				$melding = 'Exemplaar terugontvangen melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarterugontvangen()';
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar is vermist
	 * Alleen door eigenaar
	 * 
	 * /exemplaarvermist/id/exemplaarid
	 */
	protected function exemplaarvermist() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))) {
			if ($this->boek->vermistExemplaar($this->getParam(2))) {
				$melding = array('Exemplaar vermist.', 1);
			} else {
				$melding = 'Exemplaar vermist melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarvermist()';
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarvermist()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Exemplaar is gevonden
	 * Alleen door eigenaar
	 * 
	 * /exemplaargevonden/id/exemplaarid
	 */
	protected function exemplaargevonden() {
		$this->loadBoek();
		if ($this->hasParam(2) AND $this->boek->isEigenaar($this->getParam(2))) {
			if ($this->boek->gevondenExemplaar($this->getParam(2))) {
				$melding = array('Exemplaar gevonden.', 1);
			} else {
				$melding = 'Exemplaar gevonden melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaargevonden()';
			}
		} else {
			$melding = 'Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaargevonden()';
		}
		invokeRefresh(CSR_ROOT . '/communicatie/bibliotheek/boek/' . $this->boek->getId(), $melding);
	}

	/**
	 * Genereert suggesties voor jquery-autocomplete
	 * 
	 * /autocomplete/auteur
	 * 
	 * @return json
	 */
	protected function autocomplete() {
		if ($this->hasParam(1)) {
			echo json_encode(Catalogus::getAutocompleteSuggesties($this->getParam(1)));
		}
		exit;
	}

}
