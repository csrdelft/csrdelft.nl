<?php

require_once 'model/maalcie/MaaltijdenModel.class.php';
require_once 'model/maalcie/ArchiefMaaltijdModel.class.php';
require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';
require_once 'model/maalcie/MaaltijdRepetitiesModel.class.php';
require_once 'view/maalcie/BeheerMaaltijdenView.class.php';
require_once 'view/maalcie/forms/MaaltijdForm.class.php';
require_once 'view/maalcie/forms/RepetitieMaaltijdenForm.class.php';
require_once 'view/maalcie/forms/AanmeldingForm.class.php';

/**
 * BeheerMaaltijdenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BeheerMaaltijdenController extends AclController {

    /**
     * @var MaaltijdenModel
     */
    protected $model;

	public function __construct($query) {
		parent::__construct($query, MaaltijdenModel::transaction());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer'	 => 'P_MAAL_MOD',
				'prullenbak' => 'P_MAAL_MOD',
				//'leegmaken' => 'P_MAAL_MOD',
				'archief'	 => 'P_MAAL_MOD',
				'fiscaal'	 => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'beheer'         => 'P_MAAL_MOD',
				'prullenbak'     => 'P_MAAL_MOD',
				'sluit'			 => 'P_MAAL_MOD',
				'open'			 => 'P_MAAL_MOD',
				'toggle'         => 'P_MAAL_MOD',
				'nieuw'			 => 'P_MAAL_MOD',
				'bewerk'		 => 'P_MAAL_MOD',
				'verwijder'		 => 'P_MAAL_MOD',
				'herstel'		 => 'P_MAAL_MOD',
				'aanmelden' => 'P_MAAL_MOD',
				'afmelden'	 => 'P_MAAL_MOD',
				'aanmaken'		 => 'P_MAAL_MOD'
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
			$mid = (int) $this->getParam(3);
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
			$this->view->addCompressedResources('maalcie');
			$this->view->addCompressedResources('datatable');
		}
	}

	public function beheer($mid = null) {
		if ($this->getMethod() == 'POST') {
			$filter = $this->hasParam('filter') ? $this->getParam('filter') : '';
			switch ($filter) {
				case 'prullenbak':
					$data = $this->model->find('verwijderd = true');
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
		} else {
			$modal = null;
			if (is_int($mid) && $mid > 0) {
				$this->bewerk($mid);
				$modal = $this->view;
			}
			$repetities = MaaltijdRepetitiesModel::instance()->find(); /** @var MaaltijdRepetitie[] $repetities */
			$body = new BeheerMaaltijdenView(new BeheerMaaltijdenTable($repetities), 'Maaltijdenbeheer');
			$this->view = new CsrLayoutPage($body, array(), $modal);
			$this->view->addCompressedResources('maalcie');
			$this->view->addCompressedResources('datatable');
		}
	}

	public function archief() {
		$body = new BeheerMaaltijdenView(ArchiefMaaltijdModel::instance()->getArchiefMaaltijdenTussen(), false, true);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('maalcie');
	}

	public function fiscaal($mid) {
		$maaltijd = $this->model->getMaaltijd($mid, true);
		$aanmeldingen = MaaltijdAanmeldingenModel::instance()->getAanmeldingenVoorMaaltijd($maaltijd);
		require_once 'view/maalcie/MaaltijdLijstView.class.php';
		$this->view = new MaaltijdLijstView($maaltijd, $aanmeldingen, null, true);
	}

	public function toggle($mid) {
		$maaltijd = $this->model->getMaaltijd($mid);

		if ($maaltijd->gesloten) {
			$this->model->openMaaltijd($maaltijd);
		} else {
			$this->model->sluitMaaltijd($maaltijd);
		}

		$this->view = new BeheerMaaltijdenLijst(array($maaltijd));
	}

	public function nieuw() {
		$maaltijd = new Maaltijd();
		$this->view = new MaaltijdForm($maaltijd, 'nieuw');

		if ($this->view->validate()) {
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
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd;
				$maaltijd->prijs = $repetitie->standaard_prijs;
				$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
			}
		}

	}

	public function bewerk() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (empty($selection)) {
			$this->exit_http(403);
		}
		$maaltijd = $this->model->retrieveByUUID($selection[0]);
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
		$maaltijd = $this->model->retrieveByUUID($selection[0]); /** @var Maaltijd $maaltijd */

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
		$maaltijd = $this->model->retrieveByUUID($selection[0]); /** @var Maaltijd $maaltijd */

		$maaltijd->verwijderd = false;
		$this->model->update($maaltijd);
		$this->view = new RemoveRowsResponse(array($maaltijd)); // Verwijder uit prullenbak
	}

	public function aanmelden() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$maaltijd = $this->model->retrieveByUUID($selection[0]); /** @var Maaltijd $maaltijd */
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
				throw new Exception('Geen nieuwe maaltijden aangemaakt');
			}
			$this->view = new BeheerMaaltijdenLijst($maaltijden);
		} else {
			$this->view = $form;
		}
	}

}
