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
use CsrDelft\view\maalcie\beheer\FiscaatMaaltijdenOverzichtResponse;
use CsrDelft\view\maalcie\beheer\FiscaatMaaltijdenOverzichtTable;
use CsrDelft\view\maalcie\beheer\OnverwerkteMaaltijdenTable;

/**
 * MaaltijdenFiscaatController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MaaltijdenFiscaatController {
	/**
	 * @var CiviProductModel
	 */
	private $civiProductModel;
	/**
	 * @var MaaltijdenModel
	 */
	private $maaltijdenModel;
	/**
	 * @var MaaltijdAanmeldingenModel
	 */
	private $maaltijdAanmeldingenModel;
	/**
	 * @var CiviBestellingModel
	 */
	private $civiBestellingModel;
	/**
	 * @var CiviSaldoModel
	 */
	private $civiSaldoModel;

	public function __construct(
		CiviProductModel $civiProductModel,
		MaaltijdenModel $maaltijdenModel,
		MaaltijdAanmeldingenModel $maaltijdAanmeldingenModel,
		CiviBestellingModel $civiBestellingModel,
		CiviSaldoModel $civiSaldoModel
	) {
		$this->civiProductModel = $civiProductModel;
		$this->maaltijdenModel = $maaltijdenModel;
		$this->maaltijdAanmeldingenModel = $maaltijdAanmeldingenModel;
		$this->civiBestellingModel = $civiBestellingModel;
		$this->civiSaldoModel = $civiSaldoModel;
	}

	public function GET_overzicht() {
		return view('maaltijden.pagina', [
			'titel' => 'Overzicht verwerkte maaltijden',
			'content' => new FiscaatMaaltijdenOverzichtTable(),
		]);
	}

	public function POST_overzicht() {
		$data = $this->maaltijdenModel->find('verwerkt = true');
		return new FiscaatMaaltijdenOverzichtResponse($data);
	}

	public function GET_onverwerkt() {
		return view('maaltijden.pagina', [
			'titel' => 'Onverwerkte Maaltijden',
			'content' => new OnverwerkteMaaltijdenTable(),
		]);
	}

	public function POST_verwerk() {
		# Haal maaltijd op
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		/** @var Maaltijd $maaltijd */
		$maaltijd = $this->maaltijdenModel->retrieveByUUID($selection[0]);

		# Controleer of de maaltijd gesloten is en geweest is
		if ($maaltijd->gesloten == false OR date_create(sprintf("%s %s", $maaltijd->datum, $maaltijd->tijd)) >= date_create("now")) {
			throw new CsrGebruikerException("Maaltijd nog niet geweest");
		}

		# Controleer of maaltijd niet al verwerkt is
		if ($maaltijd->verwerkt) {
			throw new CsrGebruikerException("Maaltijd is al verwerkt");
		}

		$maaltijden = Database::transaction(function () use ($maaltijd) {
			# Ga alle personen in de maaltijd af
			$aanmeldingen = $this->maaltijdAanmeldingenModel->find('maaltijd_id = ?', array($maaltijd->maaltijd_id));

			/** @var Civibestelling[] $bestellingen */
			$bestellingen = array();
			# Maak een bestelling voor deze persoon
			foreach ($aanmeldingen as $aanmelding) {
				$bestellingen[] = $this->maaltijdAanmeldingenModel->maakCiviBestelling($aanmelding);
			}

			# Reken de bestelling af
			foreach ($bestellingen as $bestelling) {
				$this->civiBestellingModel->create($bestelling);
				$this->civiSaldoModel->verlagen($bestelling->uid, $bestelling->totaal);
			}

			# Zet de maaltijd op verwerkt
			$maaltijd->verwerkt = true;

			$this->maaltijdenModel->update($maaltijd);

			return array($maaltijd);
		});

		return new RemoveRowsResponse($maaltijden);
	}

}
