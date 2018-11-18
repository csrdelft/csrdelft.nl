<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\Controller;
use CsrDelft\model\bibliotheek\BoekExemplaarModel;
use CsrDelft\model\bibliotheek\BoekImporter;
use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\bibliotheek\BoekRecensieModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\bibliotheek\BoekExemplaar;
use CsrDelft\model\entity\bibliotheek\BoekRecensie;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bibliotheek\BibliotheekBoekView;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatable;
use CsrDelft\view\bibliotheek\BibliotheekCatalogusDatatableResponse;
use CsrDelft\view\bibliotheek\BoekExemplaarFormulier;
use CsrDelft\view\bibliotheek\BoekFormulier;
use CsrDelft\view\bibliotheek\RecensieFormulier;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;
use dokuwiki\Action\Login;

/**
 * BibliotheekController.class.php  |  Gerrit Uitslag (klapinklapin@gmail.com)
 *
 */
class BibliotheekController extends Controller {

	public $baseurl = '/bibliotheek/';

	/**
	 * query:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($query) {
		parent::__construct($query, BoekModel::instance());
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'catalogustonen';
		}
	}

	public function performAction(array $args = array()) {
		if (in_array($this->action, ['boek', 'import', 'addexemplaar'])) {
			if ($this->hasParam(4) && $this->getParam(4) == "recensie") {
				$this->action = "recensie";
			}
			$boek = null;
			if ($this->hasParam(3)) {
				$boek = BoekModel::instance()->get($this->getParam(3));
			} else {
				$boek = new Boek();
			}
			parent::performAction([$boek]);
		} else if (in_array($this->action, ['verwijderbeschrijving']) && $this->hasParam(3) && $this->hasParam(4)) {
			$recensie = BoekRecensieModel::instance()->get($this->getParam(3), $this->getParam(4));
			parent::performAction([$recensie]);
		}
		else if (in_array($this->action, ['exemplaar', 'exemplaarlenen', 'exemplaarteruggegeven', 'exemplaarterugontvangen', 'deleteexemplaar', 'exemplaaruitlenen', 'exemplaarvermist', 'exemplaargevonden']) && $this->hasParam(3)) {
			$exemplaar = BoekExemplaarModel::instance()->get($this->getParam(3));
			parent::performAction([$exemplaar]);
		} else {
			parent::performAction($args);
		}
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
				'boek', 'recensie'
			));
			if ($this->getMethod() == 'POST') {
				$allow = array_merge($allow,
					['verwijderboek',
						'verwijderbeschrijving',
						'exemplaar',
						'addexemplaar', 'verwijderexemplaar',
						'exemplaarlenen', 'exemplaarteruggegeven', 'exemplaarterugontvangen', 'exemplaarvermist', 'exemplaargevonden', 'import', 'nieuwRecensie']);
			}
		}
		if (!in_array($action, $allow)) {
			$this->action = 'catalogustonen';
		}
		return true;
	}


	public function recensie(Boek $boek) {
		$recensie = BoekRecensieModel::get($boek->id, LoginModel::getUid());
		$formulier = new RecensieFormulier($recensie);
		if ($formulier->validate()) {
			if (!$recensie->magBewerken()) {
				throw new CsrToegangException("Mag recensie niet bewerken", 403);
			} else {
				$recensie->bewerkdatum = getDateTime();
				BoekRecensieModel::instance()->updateOrCreate($recensie);
				setMelding("Recensie opgeslagen", 0);
			}
		}
		redirect("/bibliotheek/boek/$boek->id");
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
		$this->view = new BibliotheekCatalogusDatatable();
	}

	/**
	 * Inhoud voor tabel op de cataloguspagina ophalen
	 */
	protected function catalogusdata() {
		/**
		 * @var Boek[] $data
		 */
		$data = $this->model->find()->fetchAll();
		$uid = filter_input(INPUT_GET, "eigenaar", FILTER_SANITIZE_STRING);
		$results = [];
		if ($uid !== null) {
			foreach ($data as $boek) {
				if ($boek->isEigenaar($uid)) {
					$results[] = $boek;
				}
			}
		} else {
			$results = $data;
		}
		$this->view = new BibliotheekCatalogusDatatableResponse($results);
		$this->view->view();
		exit;
	}

	/**
	 * Boek weergeven
	 * @param Boek $boek
	 */
	protected function boek(Boek $boek) {
		$boekForm = new BoekFormulier($boek);

		if ($boekForm->validate()) {
			if (!$boek->magBewerken()) {
				throw new CsrToegangException('U mag dit boek niet bewerken');
			} else {
				$boekid = BoekModel::instance()->updateOrCreate($boek);
				if ($boekid !== false) {
					redirect("/bibliotheek/boek/$boekid");
				}
			}
		}

		$alleRecensies = $boek->getRecensies();
		$andereRecensies = [];
		$mijnRecensie = new BoekRecensie();
		$mijnRecensie->boek_id = $boek->id;
		$exemplaarFormulieren = [];
		foreach ($boek->getExemplaren() as $exemplaar) {
			if ($exemplaar->magBewerken()) {
				$exemplaarFormulieren[$exemplaar->id] = new BoekExemplaarFormulier($exemplaar);
			}
		}
		foreach ($alleRecensies as $recensie) {
			if ($recensie->schrijver_uid == LoginModel::getUid()) {
				$mijnRecensie = $recensie;
			}
			$andereRecensies[] = $recensie;

		}
		$recensieForm = new RecensieFormulier($mijnRecensie);
		$this->view = new BibliotheekBoekView($boek, $boekForm, $andereRecensies, $recensieForm, $exemplaarFormulieren);
	}

	protected function import(Boek $boek) {
		if (!$boek->isEigenaar()) {
			$this->exit_http(403);
		} else {
			$importer = new BoekImporter();
			$importer->import($boek);
			BoekModel::instance()->update($boek);
			redirect("/bibliotheek/boek/$boek->id");
		}
	}


	protected function verwijderbeschrijving(BoekRecensie $recensie) {
		if (!$recensie->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
			$this->exit_http(403);
		} else {
			BoekRecensieModel::instance()->delete($recensie);
			setMelding('Recensie met succes verwijderd.', 1);

		}
		exit;
	}

	/**
	 * Verwijder boek
	 *
	 * /verwijderboek/id
	 */
	protected function verwijderboek(Boek $boek) {

		if (!$boek->magVerwijderen()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addbeschrijving', -1);
			redirect('/bibliotheek/');
		} else {
			$this->model->delete($boek);
			setMelding('Boek met succes verwijderd.', 1);
			redirect('/bibliotheek/');
		}
	}
	protected function exemplaar (BoekExemplaar $exemplaar) {
		if (!$exemplaar->magBewerken()) {
			throw new CsrToegangException("Mag exemplaar niet bewerken", 403);
		}
		$form = new BoekExemplaarFormulier($exemplaar);
		if($form->validate()) {
			BoekExemplaarModel::instance()->update($exemplaar);
		}
		redirect('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
	}

	/**
	 * Exemplaar toevoegen
	 * /addexemplaar/$boekid[/$eigenaarid]
	 */
	protected function addexemplaar(Boek $boek) {
		if (!$boek->magBekijken()) {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::addexemplaar()', -1);
			redirect('/bibliotheek/boek/' . $boek->id);
		}
		if ($this->hasParam(4)) {
			$eigenaar = $this->getParam(4);
		} else {
			$eigenaar = LoginModel::getUid();
		}
		if ($eigenaar != LoginModel::getUid() && !($eigenaar == 'x222' && LoginModel::mag('P_BIEB_MOD'))) {
			throw new CsrToegangException('Mag deze eigenaar niet kiezen');
		}
		BoekExemplaarModel::addExemplaar($boek, $eigenaar);

		setMelding('Exemplaar met succes toegevoegd.', 1);
		redirect('/bibliotheek/boek/' . $boek->id);
	}

	/**
	 * Exemplaar verwijderen
	 * /deleteexemplaar/$exemplaarid
	 */
	protected function verwijderexemplaar(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			if (BoekExemplaarModel::instance()->delete($exemplaar)) {
				setMelding('Exemplaar met succes verwijderd.', 1);
			} else {
				setMelding('Exemplaar verwijderen mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		redirect('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaarvermist/[id]
	 */
	protected function exemplaarvermist(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			if (BoekExemplaarModel::setVermist($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als vermist.', 1);
			} else {
				setMelding('Exemplaar markeren als vermist mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		redirect('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
	}

	/**
	 * Exemplaar als vermist markeren
	 * /exemplaargevonden/[id]
	 */
	protected function exemplaargevonden(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar()) {
			if (BoekExemplaarModel::setGevonden($exemplaar)) {
				setMelding('Exemplaar gemarkeerd als gevonden.', 1);
			} else {
				setMelding('Exemplaar markeren als gevonden mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie.', -1);
		}
		$this->view = new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->id);
		exit;
	}

	/**
	 * /exemplaaruitlenen/[exemplaarid]
	 */
	protected function exemplaaruitlenen(BoekExemplaar $exemplaar) {
		$uid = filter_input(INPUT_POST, 'lener_uid', FILTER_SANITIZE_STRING);
		if (!$exemplaar->isEigenaar()) {
			setMelding('Alleen de eigenaar mag boeken uitlenen', -1);
		} else if (!ProfielModel::existsUid($uid)) {
			setMelding('Incorrecte lener', -1);
		} else if (BoekExemplaarModel::leen($exemplaar, $uid)) {
			redirect('/bibliotheek/boek/' . $exemplaar->getBoek()->getId() . '#exemplaren');
		} else {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}
	}


	/**
	 * /exemplaarlenen/[exemplaarid]
	 */
	protected function exemplaarlenen(BoekExemplaar $exemplaar) {
		if (BoekExemplaarModel::leen($exemplaar, LoginModel::getUid())) {
			redirect('/bibliotheek/boek/' . $exemplaar->getBoek()->getId() . '#exemplaren');
		} else {
			setMelding('Kan dit exemplaar niet lenen', -1);
		}
	}


	/**
	 * Lener zegt dat hij/zij exemplaar heeft teruggegeven
	 * Alleen door lener
	 *
	 * /exemplaarteruggegeven/[exemplaarid]
	 */
	protected function exemplaarteruggegeven(BoekExemplaar $exemplaar) {
		if ($exemplaar->isUitgeleend() && $exemplaar->uitgeleend_uid == LoginModel::getUid()) {
			if (BoekExemplaarModel::terugGegeven($exemplaar)) {
				setMelding('Exemplaar is teruggegeven.', 1);
			} else {
				setMelding('Teruggave van exemplaar melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. ', -1);
		}
		$this->view = new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->getId());
		exit;
	}

	/**
	 * Exemplaar is terugontvangen van lener
	 * Alleen door eigenaar
	 *
	 * /exemplaarterugontvangen/exemplaarid
	 */
	protected function exemplaarterugontvangen(BoekExemplaar $exemplaar) {
		if ($exemplaar->isEigenaar() && ($exemplaar->isUitgeleend() || $exemplaar->isTeruggegeven())) {
			if (BoekExemplaarModel::terugOntvangen($exemplaar)) {
				setMelding('Exemplaar terugontvangen.', 1);
			} else {
				setMelding('Exemplaar terugontvangen melden is mislukt. ', -1);
			}
		} else {
			setMelding('Onvoldoende rechten voor deze actie. Biebcontrllr::exemplaarterugontvangen()', -1);
		}
		$this->view = new JsonResponse('/bibliotheek/boek/' . $exemplaar->getBoek()->getId());
		exit;
	}


	/**
	 * Genereert suggesties voor jquery-autocomplete
	 *
	 * /autocomplete/auteur
	 *
	 */
	protected function autocomplete() {
		if ($this->hasParam(3) AND isset($_GET['q'])) {

			$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

			$categorie = 0;
			if ($this->hasParam(4)) {
				$categorie = (int)$this->getParam(4);
			}
			$results = BoekModel::autocompleteProperty($this->getParam(3), $zoekterm);
			$data = [];
			foreach ($results as $result) {
				$data[] = ['data' => [$result], 'value' => $result, 'result' => $result];
			}
			$this->view = new JsonResponse($data);
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
		foreach (BoekModel::autocompleteBoek($zoekterm) as $boek) {
			$result[] = array(
				'url' => '/bibliotheek/boek/' . $boek->id,
				'label' => $boek->auteur,
				'value' => $boek->titel
			);
		}
		$this->view = new JsonResponse($result);
		$this->view->view();
		exit;
	}


}
