<?php

use CsrDelft\Orm\Persistence\Database;

require_once 'model/maalcie/MaaltijdenModel.class.php';
require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';
require_once 'model/fiscaat/CiviBestellingModel.class.php';
require_once 'model/fiscaat/CiviSaldoModel.class.php';
require_once 'view/maalcie/BeheerMaaltijdenView.class.php';
require_once 'view/fiscaat/BeheerCiviProductenView.class.php';

/**
 * MaaltijdenFiscaatController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property MaaltijdenModel $model
 *
 */
class MaaltijdenFiscaatController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CiviProductModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'onverwerkt' => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'verwerk' => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'onverwerkt';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mid = null;
		if ($this->hasParam(3)) {
			$mid = (int)$this->getParam(3);
		}
		parent::performAction(array($mid));
	}

	public function GET_onverwerkt() {
		$body = new BeheerMaaltijdenView(new OnverwerkteMaaltijdenTable(), 'Onverwerkte Maaltijden');
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('maalcie');
		$this->view->addCompressedResources('datatable');
	}

	public function POST_verwerk() {
		# Haal maaltijd op
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = MaaltijdenModel::instance()->retrieveByUUID($selection[0]);

		# Controleer of de maaltijd gesloten is en geweest is
		if ($maaltijd->gesloten == false OR date_create(sprintf("%s %s", $maaltijd->datum, $maaltijd->tijd)) >= date_create("now")) {
			throw new Exception("Maaltijd nog niet geweest");
		}

		$maaltijden = Database::transaction(function () use ($maaltijd) {
			$aanmeldingen_model = MaaltijdAanmeldingenModel::instance();
			$bestelling_model = CiviBestellingModel::instance();
			$civisaldo_model = CiviSaldoModel::instance();

			# Ga alle personen in de maaltijd af
			$aanmeldingen = $aanmeldingen_model->find('maaltijd_id = ?', array($maaltijd->maaltijd_id));

			/** @var Civibestelling[] $bestellingen */
			$bestellingen = array();
			# Maak een bestelling voor deze persoon
			foreach ($aanmeldingen as $aanmelding) {
				$bestellingen[] = $bestelling_model->vanMaaltijdAanmelding($aanmelding);
			}

			# Reken de bestelling af
			foreach ($bestellingen as $bestelling) {
				$bestelling_model->create($bestelling);
				$civisaldo_model->verlagen($bestelling->uid, $bestelling->totaal);
			}

			# Zet de maaltijd op verwerkt
			$maaltijd->verwerkt = true;

			MaaltijdenModel::instance()->update($maaltijd);

			return array($maaltijd);
		});

		$this->view = new RemoveRowsResponse($maaltijden);
	}

}
