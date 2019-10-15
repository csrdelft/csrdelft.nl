<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviProductModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\maalcie\MaaltijdAanmeldingenModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\maalcie\beheer\BeheerMaaltijdenView;
use CsrDelft\view\maalcie\beheer\FiscaatMaaltijdenOverzichtResponse;
use CsrDelft\view\maalcie\beheer\FiscaatMaaltijdenOverzichtTable;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;

/**
 * MaaltijdenFiscaatController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaaltijdenFiscaatController {
	private $model;

	public function __construct() {
		$this->model = CiviProductModel::instance();
	}

	public function GET_overzicht() {
		$body = new BeheerMaaltijdenView(new FiscaatMaaltijdenOverzichtTable(), 'Overzicht verwerkte maaltijden');
		return view('default', ['content' => $body]);
	}

	public function POST_overzicht() {
		$data = MaaltijdenModel::instance()->find('verwerkt = true');
		return new FiscaatMaaltijdenOverzichtResponse($data);
	}

	public function GET_onverwerkt() {
		$body = new BeheerMaaltijdenView(new OnverwerkteMaaltijdenTable(), 'Onverwerkte Maaltijden');
		return view('default', ['content' => $body]);
	}

	public function POST_verwerk() {
		# Haal maaltijd op
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = MaaltijdenModel::instance()->retrieveByUUID($selection[0]);

		# Controleer of de maaltijd gesloten is en geweest is
		if ($maaltijd->gesloten == false OR date_create(sprintf("%s %s", $maaltijd->datum, $maaltijd->tijd)) >= date_create("now")) {
			throw new CsrGebruikerException("Maaltijd nog niet geweest");
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
				$bestellingen[] = $aanmeldingen_model->maakCiviBestelling($aanmelding);
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

		return new RemoveRowsResponse($maaltijden);
	}

}
