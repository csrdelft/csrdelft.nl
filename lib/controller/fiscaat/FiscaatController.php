<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\Routing\Annotation\Route;

class FiscaatController {
	/** @var CiviSaldoRepository */
	private $civiSaldoRepository;

	public function __construct(CiviSaldoRepository $civiSaldoRepository) {
		$this->civiSaldoRepository = $civiSaldoRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/fiscaat")
	 * @Auth(P_FISCAAT_READ)
	 */
	public function overzicht() {
		return view('fiscaat.overzicht', [
			'saldisomform' => new SaldiSomForm($this->civiSaldoRepository),
			'saldisom' => $this->civiSaldoRepository->getSomSaldi(),
			'saldisomleden' => $this->civiSaldoRepository->getSomSaldi(true),
			'productenbeheer' => new CiviProductTable(),
			'saldobeheer' => new CiviSaldoTable(),
		]);
	}
}
