<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FiscaatController extends AbstractController {
	/** @var CiviSaldoRepository */
	private $civiSaldoRepository;

	public function __construct(CiviSaldoRepository $civiSaldoRepository) {
		$this->civiSaldoRepository = $civiSaldoRepository;
	}

	/**
	 * @return Response
	 * @Route("/fiscaat")
	 * @Auth(P_FISCAAT_READ)
	 */
	public function overzicht() {
		return $this->render('fiscaat/overzicht.html.twig', [
			'saldisomform' => new SaldiSomForm($this->civiSaldoRepository),
			'saldisom' => $this->civiSaldoRepository->getSomSaldi(),
			'saldisomleden' => $this->civiSaldoRepository->getSomSaldi(true),
			'productenbeheer' => new CiviProductTable(),
			'saldobeheer' => $this->createDataTable(CiviSaldoTable::class),
		]);
	}
}
