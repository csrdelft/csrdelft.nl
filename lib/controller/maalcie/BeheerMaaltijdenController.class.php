<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\ArchiefMaaltijdModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\maalcie\beheer\ArchiefMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenLijst;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenView;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;
use CsrDelft\view\maalcie\beheer\PrullenbakMaaltijdenTable;
use CsrDelft\view\maalcie\forms\AanmeldingForm;
use CsrDelft\view\maalcie\forms\MaaltijdForm;
use CsrDelft\view\maalcie\forms\RepetitieMaaltijdenForm;

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdenModel $model
 *
 */
class BeheerMaaltijdenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, MaaltijdenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD',
				'prullenbak' => 'P_MAAL_MOD',
				//'leegmaken' => 'P_MAAL_MOD',
				'archief' => 'P_MAAL_MOD',
				'onverwerkt' => 'P_MAAL_MOD',
			);
		} else {
			$this->acl = array(
				'beheer' => 'P_MAAL_MOD',
				'prullenbak' => 'P_MAAL_MOD',
				'archief' => 'P_MAAL_MOD',
				'sluit' => 'P_MAAL_MOD',
				'open' => 'P_MAAL_MOD',
				'toggle' => 'P_MAAL_MOD',
				'nieuw' => 'P_MAAL_MOD',
				'bewerk' => 'P_MAAL_MOD',
				'verwijder' => 'P_MAAL_MOD',
				'herstel' => 'P_MAAL_MOD',
				'aanmelden' => 'P_MAAL_MOD',
				'afmelden' => 'P_MAAL_MOD',
				'aanmaken' => 'P_MAAL_MOD',
				'verwerk' => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mid = null;
		if ($this->hasParam(3)) {
			$mid = (int)$this->getParam(3);
		}
		parent::performAction(array($mid));
	}

	public function prullenbak() {
		if ($this->getMethod() == 'POST') {
			$data = $this->model->find('verwijderd = true');
			$this->view = new BeheerMaaltijdenLijst($data);
		} else {
			$body = new BeheerMaaltijdenView(new PrullenbakMaaltijdenTable(), 'Prullenbak maaltijdenbeheer');
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function POST_beheer() {
		$filter = $this->hasParam('filter') ? $this->getParam('filter') : '';
		switch ($filter) {
			case 'prullenbak':
				$data = $this->model->find('verwijderd = true');
				break;
			case 'onverwerkt':
				$data = $this->model->find('verwijderd = false AND gesloten = true AND verwerkt = false');
				break;
			case 'alles':
				$data = $this->model->getMaaltijden();
				break;
			case 'toekomst':
			default:
				$data = $this->model->getMaaltijden('datum > NOW() - INTERVAL 1 WEEK');
				break;
		}

		$this->view = new BeheerMaaltijdenLijst($data);
	}

	public function GET_beheer($mid = null) {
		$modal = null;
		if ($mid !== null) {
			$this->bewerk($mid);
			$modal = $this->view;
		} elseif ($mid === 0) {
			$this->nieuw();
			$modal = $this->view;
		}
		/** @var MaaltijdRepetitie[] $repetities */
		$repetities = MaaltijdRepetitiesModel::instance()->find();
		$body = new BeheerMaaltijdenView(new BeheerMaaltijdenTable($repetities), 'Maaltijdenbeheer');
		$this->view = new CsrLayoutPage($body, array(), $modal);
		$this->view->addCompressedResources('datatable');
	}

	public function archief() {
		if ($this->getMethod() == 'POST') {
			$data = ArchiefMaaltijdModel::instance()->find();
			$this->view = new BeheerMaaltijdenLijst($data);
		} else {
			$body = new BeheerMaaltijdenView(new ArchiefMaaltijdenTable(), 'Archief maaltijdenbeheer');
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function toggle($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);

		if ($maaltijd->verwerkt) {
			throw new CsrGebruikerException('Maaltijd al verwerkt');
		}

		if ($maaltijd->gesloten) {
			$this->model->openMaaltijd($maaltijd);
		} else {
			$this->model->sluitMaaltijd($maaltijd);
		}

		$this->view = new BeheerMaaltijdenLijst(array($maaltijd));
	}

	public function nieuw() {
		$maaltijd = new Maaltijd();
		$form = new MaaltijdForm($maaltijd, 'nieuw');

		if ($form->validate()) {
			$maaltijd_aanmeldingen = $this->model->saveMaaltijd($maaltijd);
			$this->view = new BeheerMaaltijdenLijst(array($maaltijd_aanmeldingen[0]));
			if ($maaltijd_aanmeldingen[1] > 0) {
				setMelding($maaltijd_aanmeldingen[1] . ' aanmelding' . ($maaltijd_aanmeldingen[1] !== 1 ? 'en' : '') . ' verwijderd vanwege aanmeldrestrictie: ' . $maaltijd_aanmeldingen[0]->aanmeld_filter, 2);
			}
		} elseif ($this->hasParam('mrid')) {
			$mrid = $this->getParam('mrid');
			$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
			$beginDatum = $repetitie->getFirstOccurrence();
			if ($repetitie->periode_in_dagen > 0) {
				$this->view = new RepetitieMaaltijdenForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
			} else {
				$maaltijd->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
				$maaltijd->product_id = $repetitie->product_id;
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd;
				$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
				$this->view = new MaaltijdForm($maaltijd, 'nieuw');
			}
		} else {
			$this->view = $form;
		}

	}

	public function bewerk($mid = null) {
		if ($mid === null) {
			$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			if (empty($selection)) {
				$this->exit_http(404);
			}
			$mid = $selection[0];
		}

		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->model->retrieveByUUID($mid);
		$form = new MaaltijdForm($maaltijd, 'bewerk');
		if ($form->validate()) {
			$this->model->update($maaltijd);
			$this->view = new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			$this->view = $form;
		}
	}

	public function verwijder() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->model->retrieveByUUID($selection[0]);

		if ($maaltijd->verwijderd) {
			$this->model->delete($maaltijd);
		} else {
			$maaltijd->verwijderd = true;
			$this->model->update($maaltijd);
		}

		$this->view = new RemoveRowsResponse(array($maaltijd));
	}

	public function herstel() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->model->retrieveByUUID($selection[0]);

		$maaltijd->verwijderd = false;
		$this->model->update($maaltijd);
		$this->view = new RemoveRowsResponse(array($maaltijd)); // Verwijder uit prullenbak
	}

	public function aanmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->model->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, true); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			MaaltijdAanmeldingenModel::instance()->aanmeldenVoorMaaltijd($maaltijd, $values['voor_lid'], LoginModel::getUid(), $values['aantal_gasten'], true);
			$this->view = new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			$this->view = $form;
		}
	}

	public function afmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->model->retrieveByUUID($selection[0]);
		$form = new AanmeldingForm($maaltijd, false); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			MaaltijdAanmeldingenModel::instance()->afmeldenDoorLid($maaltijd, $values['voor_lid'], true);
			$this->view = new BeheerMaaltijdenLijst(array($maaltijd));
		} else {
			$this->view = $form;
		}
	}

	public function leegmaken() {
		$aantal = $this->model->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' maaltijd' : ' maaltijden') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		redirect(maalcieUrl . '/prullenbak');
	}

	// Repetitie-Maaltijden ############################################################

	public function aanmaken($mrid) {
		$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
		$form = new RepetitieMaaltijdenForm($repetitie); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijden = $this->model->maakRepetitieMaaltijden($repetitie, strtotime($values['begindatum']), strtotime($values['einddatum']));
			if (empty($maaltijden)) {
				throw new CsrGebruikerException('Geen nieuwe maaltijden aangemaakt.');
			}
			$this->view = new BeheerMaaltijdenLijst($maaltijden);
		} else {
			$this->view = $form;
		}
	}

	// Maalcie-fiscaat

	public function onverwerkt() {
		if ($this->getAction() == "POST") {

		} else {
			$body = new BeheerMaaltijdenView(new OnverwerkteMaaltijdenTable(), 'Onverwerkte Maaltijden');
			$this->view = new CsrLayoutPage($body);
			$this->view->addCompressedResources('datatable');
		}
	}
}
