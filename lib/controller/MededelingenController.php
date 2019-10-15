<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\mededelingen\Mededeling;
use CsrDelft\model\mededelingen\MededelingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\mededelingen\MededelingenOverzichtView;
use CsrDelft\view\mededelingen\MededelingenView;
use CsrDelft\view\mededelingen\MededelingView;

/**
 * Class MededelingenController
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Controller van het bijbelrooster.
 *
 * @property MededelingenModel $model
 */
class MededelingenController extends AclController {

	/**
	 * @var bool
	 */
	private $prullenbak = false;

	public function __construct($query) {
		parent::__construct($query, MededelingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'lijst' => P_LOGGED_IN,
				'bekijken' => P_LOGGED_IN,
				'bewerken' => P_NEWS_POST,
				'verwijderen' => P_NEWS_POST,
				'toevoegen' => P_NEWS_POST,
				'top3overzicht' => P_NEWS_MOD,
				'goedkeuren' => P_NEWS_MOD
			);
		} else {
			$this->acl = array(
				'bewerken' => P_NEWS_POST
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'bekijken';
		$base = 2; // Om prullenbak ertussenuit te halen
		if ($this->hasParam(2) && $this->getParam(2) == 'prullenbak') {
			// /mededelingen/prullenbak/*
			$this->prullenbak = true;
			$base = 3;
		}

		if ($this->hasParam($base)) {
			if ($this->getParam($base) == 'pagina') {
				$args = array(0) + $this->getParams($base + 1); // Id is 0
			} elseif (ctype_digit($this->getParam($base))) { // /mededelingen/{prullenbak}/xxx
				$args = $this->getParams($base);
			} else { // /mededelingen/{prullenbak?}/{bekijken|bewerken|verwijderen}/xxx
				$this->action = $this->getParam($base);
				$args = $this->getParams($base + 1);
			}
		}
		$body = parent::performAction($args);
		$this->view = view('default', ['content' => $body]);
	}

	public function top3overzicht() {
		return new MededelingenOverzichtView();
	}

	public function bekijken($id = 0, $pagina = 1) {
		return new MededelingenView($id, $pagina, $this->prullenbak);
	}

	public function toevoegen() {
		return new MededelingView(new Mededeling());
	}

	public function verwijderen($id) {
		$mededeling = $this->model->retrieveByUUID($id);

		$this->model->delete($mededeling);

		setMelding("Mededeling is verwijderd.", 1);
		if ($this->prullenbak) {
			redirect(MededelingenView::MEDEDELINGEN_ROOT . 'prullenbak');
		} else {
			redirect(MededelingenView::MEDEDELINGEN_ROOT);
		}
	}

	public function bewerken($id = 0) {
		if ($id == 0) {
			$mededeling = new Mededeling();
		} else {
			$mededeling = $this->model->retrieveByUUID($id);
		}
		if ($this->getMethod() == 'POST' && isset($_POST['titel'], $_POST['tekst'], $_POST['categorie'])) {
			if ($mededeling->datum == null) {
				$mededeling->datum = getDateTime();
			}

			$mededeling->titel = $_POST['titel'];
			$mededeling->tekst = $_POST['tekst'];
			$mededeling->categorie = $_POST['categorie'];
			$mededeling->doelgroep = $_POST['doelgroep'];
			$mededeling->uid = LoginModel::getUid();
			$mededeling->verwijderd = false;

			if (isset($_POST['prioriteit'])) {
				$mededeling->prioriteit = $_POST['prioriteit'];
			}

			if (isset($_POST['vervaltijd'])) {
				$mededeling->vervaltijd = $_POST['vervaltijd'];
			}

			if (!$this->model->isModerator()) {
				$mededeling->zichtbaarheid = 'wacht_goedkeuring';
			} else {
				$mededeling->zichtbaarheid = isset($_POST['verborgen']) ? 'onzichtbaar' : 'zichtbaar';
			}
			if (isset($_POST['verborgen'])) {
				$mededeling->verborgen = true;
			} else {
				$mededeling->verborgen = false;
			}


			if (isset($_FILES['plaatje']) && ($img_errors = $this->model->savePlaatje($_FILES['plaatje'], $mededeling)) != '') {
				setMelding('<h3>Niet opgeslagen</h3>' . $img_errors, -1);
			} elseif (($errors = $this->model->validate($mededeling)) != '') {
				setMelding('<h3>Niet opgeslagen</h3>' . $errors, -1);
			} else {
				if ($mededeling->id) {
					$this->model->update($mededeling);
					$id = $mededeling->id;
				} else {
					$id = $this->model->create($mededeling);
					// Mail de PubCie
					mail('pubcie@csrdelft.nl', 'Nieuwe mededeling wacht op goedkeuring', CSR_ROOT . '/mededelingen/' . $id . "\r\n" .
						"\r\nDe inhoud van de mededeling is als volgt: \r\n\r\n" . str_replace('\r\n', "\n", $mededeling->tekst) . "\r\n\r\nEINDE BERICHT", "From: pubcie@csrdelft.nl\nReply-To: " . $mededeling->uid . "@csrdelft.nl");
				}

				$nieuweLocatie = MededelingenView::MEDEDELINGEN_ROOT;
				if ($mededeling->verborgen) {
					$nieuweLocatie .= 'prullenbak/';
				}

				$nieuweLocatie .= $id;
				redirect($nieuweLocatie);
			}
		}
		return new MededelingView($mededeling);
	}

	public function goedkeuren($id) {
		$mededeling = $this->model->retrieveByUUID($id);
		$mededeling->zichtbaarheid = 'zichtbaar';
		$this->model->update($mededeling);
		setMelding("Mededeling is goedgekeurd.", 1);
		redirect(MededelingenView::MEDEDELINGEN_ROOT . $mededeling->id);
	}

}
