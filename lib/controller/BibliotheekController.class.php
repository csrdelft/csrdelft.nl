<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\controller\framework\Controller;
use CsrDelft\model\bibliotheek\BewerkBoek;
use CsrDelft\model\bibliotheek\BiebCatalogus;
use CsrDelft\model\bibliotheek\NieuwBoek;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bibliotheek\BibliotheekBoekContent;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusContent;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatableContent;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;

/**
 * BibliotheekController.class.php  |  Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends Controller {

	/** @var BewerkBoek|NieuwBoek */
	public $boek;
	public $baseurl = '/bibliotheek/';

	/**
	 * query:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'catalogustonen';
		}
	}

	public function performAction(array $args = array()) {
		parent::performAction($args);
		if ($this->action != "autocomplete") {
			$this->view = new CsrLayoutPage($this->view);
			$this->view->addCompressedResources('bibliotheek');
		}
	}

	protected function mag($action, array $args) {
		//iedereen(ook uitgelogd) mag catalogus bekijken.
		$allow = array('catalogustonen', 'catalogusdata', 'rubrieken', 'wenslijst');
		if (LoginModel::mag('P_BIEB_READ')) {
			$allow = array_merge($allow, array('zoeken', 'autocomplete',
				'boek', 'nieuwboek', 'bewerkboek', 'verwijderboek',
				'bewerkbeschrijving', 'verwijderbeschrijving',
				'addexemplaar', 'verwijderexemplaar',
				'exemplaarlenen', 'exemplaarteruggegeven', 'exemplaarterugontvangen', 'exemplaarvermist', 'exemplaargevonden'
			));
		}
		if (!in_array($action, $allow)) {
			$this->action = 'catalogustonen';
		}
		return true;
	}

	public function rubrieken() {
		$c = new CmsPaginaController($this->action);
		$c->bekijken($this->action);
		$c->getView()->view();
		exit;
	}

	public function wenslijst() {
		$c = new CmsPaginaController($this->action);
		$c->bekijken($this->action);
		$c->getView()->view();
		exit;
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
		$catalogus = new BiebCatalogus();
		$this->view = new BibliotheekCatalogusDatatableContent($catalogus);
		$this->view->view();
		exit;
	}

	/**
	 * Laad een boek object
	 *
	 * ga er van uit dat in getParam(3) een boekid staat en laad dat in.
	 * @param $boekid $boekid
	 *          of leeg: gebruikt getParam()
	 */
	private function loadBoek($boekid = null) {
		if ($this->hasParam(3) OR $boekid !== null) {
			if ($boekid === null) {
				$boekid = $this->getParam(3);
			}
			if ($this->hasParam(4) AND in_array($this->action, array('bewerkbeschrijving', 'verwijderbeschrijving'))) {
				$beschrijvingsid = (int)$this->getParam(4);
			} else {
				$beschrijvingsid = 0; //nieuwe beschrijving
			}
			try {
				$this->boek = new BewerkBoek($boekid, $beschrijvingsid);
			} catch (CsrException $e) {
				setMelding($e->getMessage(), -1);
				redirect('/bibliotheek/');
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
			} catch (CsrException $e) {
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
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addboek', -1);
			redirect('/bibliotheek/');
		}
		//formulier verwerken, als het onvoldoende is terug naar formulier
		if ($this->boek->validFormulier() AND $this->boek->saveFormulier()) {
			redirect('/bibliotheek/boek/' . $this->boek->getId());
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
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving', -1);
			redirect('/bibliotheek/');
		}
		if ($this->boek->delete()) {
			setMelding('Boek met succes verwijderd.', 1);
		} else {
			setMelding('Boek verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderboek()', -1);
		}
		redirect('/bibliotheek/');
	}

	/**
	 * Boekbeschrijving aanpassen
	 *
	 * /bewerkbeschrijving/id/beschrijvingsid
	 */
	protected function bewerkbeschrijving() {
		$this->loadBoek();
		if ($this->boek->getEditBeschrijving()->getId() != 0 AND !$this->boek->magBeschrijvingVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::bewerkbeschrijving()', -1);
			redirect('/bibliotheek/boek/' . $this->boek->getId());
		}
		//controleer en sla op of geef de bewerkvelden met eventuele foutmeldingen
		if ($this->boek->validFormulier() AND $this->boek->saveFormulier()) {
			redirect('/bibliotheek/boek/' . $this->boek->getId() . '#beschrijving' . $this->boek->getEditBeschrijving()->getId());
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
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::verwijderbeschrijving()', -1);
			redirect('/bibliotheek/boek/' . $this->boek->getId());
		}
		if ($this->boek->verwijderBeschrijving()) {
			setMelding('Beschrijving met succes verwijderd.', 1);
		} else {
			setMelding('Beschrijving verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderbeschrijving()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar toevoegen
	 * /addexemplaar/$boekid[/$eigenaarid]
	 */
	protected function addexemplaar() {
		$this->loadBoek();
		if (!$this->boek->magBekijken()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()', -1);
			redirect('/bibliotheek/boek/' . $this->boek->getId());
		}
		if ($this->hasParam(4)) {
			$eigenaar = $this->getParam(4);
		} else {
			$eigenaar = LoginModel::getUid();
		}
		if (AccountModel::isValidUid($eigenaar)) {
			if ($this->boek->addExemplaar($eigenaar)) {
				setMelding('Exemplaar met succes toegevoegd.', 1);
			} else {
				setMelding('Exemplaar toevoegen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::addexemplaar()', -1);
			}
		} else {
			setMelding('Ongeldig uid "' . $eigenaar . '" Biebcontrllr::addexemplaar()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$boekid/$exemplaarid
	 */
	protected function verwijderexemplaar() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->isEigenaar($this->getParam(4))) {
			if ($this->boek->verwijderExemplaar($this->getParam(4))) {
				setMelding('Exemplaar met succes verwijderd.', 1);
			} else {
				setMelding('Exemplaar verwijderen mislukt. ' . $this->boek->getError() . 'Biebcontrllr::verwijderexemplaar()', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::verwijderexemplaar()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar is geleend of wordt uitgeleend door eigenaar
	 * kan door iedereen, inclusief eigenaar
	 *
	 * /exemplaarlenen/id/exemplaarid[/ander]
	 */
	protected function exemplaarlenen() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->magBekijken()) {
			//een exemplaar wordt door eigenaar uitgeleend
			if ($this->hasParam(5) AND $this->getParam(5) == 'ander') {
				if ($this->boek->isEigenaar($this->getParam(4))) {
					if (isset($_POST['id'])) {
						if ($this->boek->validField($_POST['id']) AND $this->boek->saveField($_POST['id'])) {
							setMelding('Exemplaar uitgeleend', 1);
						} else {
							setMelding('Exemplaar uitlenen is mislukt. ' . $this->boek->getField($_POST['id'])->getError() . '- ' . $this->boek->getError() . 'Biebcontrllr::exemplaarlenen()', -1);
						}
					} else {
						setMelding('$_POST[id] is leeg', -1);
					}
				} else {
					setMelding('U moet eigenaar zijn voor deze actie. Biebcontrllr::exemplaarlenen()', -1);
				}
				// iemand leent een exemplaar
			} else {
				if ($this->boek->leenExemplaar($this->getParam(4))) {
					setMelding('Exemplaar geleend.', 1);
				} else {
					setMelding('Exemplaar lenen is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarlenen()', -1);
				}
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarlenen()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId() . '#exemplaren');
	}

	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 *
	 * /exemplaarteruggegeven/id/exemplaarid
	 */
	protected function exemplaarteruggegeven() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->isLener($this->getParam(4))) {
			if ($this->boek->teruggevenExemplaar($this->getParam(4))) {
				setMelding('Exemplaar is teruggegeven.', 1);
			} else {
				setMelding('Teruggave van exemplaar melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarteruggegeven()', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. ' . $this->boek->getError() . ' Biebcontrllr::exemplaarteruggegeven()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 *
	 * /exemplaarterugontvangen/id/exemplaarid
	 */
	protected function exemplaarterugontvangen() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->isEigenaar($this->getParam(4))) {
			if ($this->boek->terugontvangenExemplaar($this->getParam(4))) {
				setMelding('Exemplaar terugontvangen.', 1);
			} else {
				setMelding('Exemplaar terugontvangen melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarterugontvangen()', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar is vermist
	 * Alleen door eigenaar
	 *
	 * /exemplaarvermist/id/exemplaarid
	 */
	protected function exemplaarvermist() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->isEigenaar($this->getParam(4))) {
			if ($this->boek->vermistExemplaar($this->getParam(4))) {
				setMelding('Exemplaar vermist.', 1);
			} else {
				setMelding('Exemplaar vermist melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaarvermist()', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarvermist()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Exemplaar is gevonden
	 * Alleen door eigenaar
	 *
	 * /exemplaargevonden/id/exemplaarid
	 */
	protected function exemplaargevonden() {
		$this->loadBoek();
		if ($this->hasParam(4) AND $this->boek->isEigenaar($this->getParam(4))) {
			if ($this->boek->gevondenExemplaar($this->getParam(4))) {
				setMelding('Exemplaar gevonden.', 1);
			} else {
				setMelding('Exemplaar gevonden melden is mislukt. ' . $this->boek->getError() . 'Biebcontrllr::exemplaargevonden()', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaargevonden()', -1);
		}
		redirect('/bibliotheek/boek/' . $this->boek->getId());
	}

	/**
	 * Genereert suggesties voor jquery-autocomplete
	 *
	 * /autocomplete/auteur
	 *
	 * @return json
	 */
	protected function autocomplete() {
		if ($this->hasParam(3) AND isset($_GET['q'])) {

			$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

			$categorie = 0;
			if ($this->hasParam(4)) {
				$categorie = (int)$this->getParam(4);
			}

			$this->view = new JsonResponse(BiebCatalogus::getAutocompleteSuggesties($this->getParam(3), $zoekterm, $categorie));
		} else {
			$this->exit_http(403);
		}
	}

	protected function zoeken() {
		if (!$this->hasParam('q')) {
			$this->exit_http(403);
		}
		$zoekterm = $this->getParam('q');
		$categorie = 0;
		if ($this->hasParam('cat')) {
			$categorie = (int)$this->getParam('cat');
		}
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int)$this->getParam('limit');
		}
		$result = array();
		foreach (BiebCatalogus::getAutocompleteSuggesties('biebboek', $zoekterm, $categorie) as $prop) {
			$result[] = array(
				'url' => '/bibliotheek/boek/' . $prop['id'],
				'label' => $prop['auteur'],
				'value' => $prop['titel']
			);
		}
		$this->view = new JsonResponse($result);
		$this->view->view();
		exit;
	}

}
