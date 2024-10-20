<?php

namespace CsrDelft\controller\fiscaat;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use Symfony\Component\HttpFoundation\Response;

class FiscaatController extends AbstractController
{
	public function __construct(
		private readonly CiviSaldoRepository $civiSaldoRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_FISCAAT_READ)
	 */
	#[Route(path: '/fiscaat')]
	public function overzicht()
	{
		return $this->render('fiscaat/overzicht.html.twig', [
			'saldisomform' => new SaldiSomForm($this->civiSaldoRepository),
			'saldisom' => $this->civiSaldoRepository->getSomSaldi(),
			'saldisomleden' => $this->civiSaldoRepository->getSomSaldi(true),
			'productenbeheer' => new CiviProductTable(),
			'saldobeheer' => $this->createDataTable(CiviSaldoTable::class),
		]);
	}
}
