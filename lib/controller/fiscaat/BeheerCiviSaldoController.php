<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\InleggenForm;
use CsrDelft\view\fiscaat\saldo\LidRegistratieForm;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use CsrDelft\view\JsonResponse;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * BeheerCiviSaldoController.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class BeheerCiviSaldoController extends AbstractController {
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var CiviBestellingRepository
	 */
	private $civiBestellingRepository;
	/**
	 * @var ProfielService
	 */
	private $profielService;

	public function __construct(CiviSaldoRepository $civiSaldoRepository, CiviBestellingRepository $civiBestellingRepository, ProfielService $profielService) {
		$this->profielService = $profielService;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
	}

	public function overzicht() {
		return view('fiscaat.pagina', [
			'titel' => 'Saldo beheer',
			'view' => new CiviSaldoTable(),
		]);
	}

	public function lijst() {
		return $this->tableData($this->civiSaldoRepository->findBy(['deleted' => false]));
	}

	public function inleggen(EntityManagerInterface $em) {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		/** @var CiviSaldo $civisaldo */
		$civisaldo = $this->civiSaldoRepository->retrieveByUUID($selection[0]);

		if ($civisaldo) {
			$form = new InleggenForm($civisaldo);
			$values = $form->getValues();
			if ($form->validate() AND $values['inleg'] !== 0 AND $values['saldo'] == $civisaldo->saldo) {
				$inleg = $values['inleg'];
				$em->transactional(function () use ($inleg, $civisaldo) {
					$bestelling = $this->civiBestellingRepository->vanBedragInCenten($inleg, $civisaldo->uid);
					$this->civiBestellingRepository->create($bestelling);

					$this->civiSaldoRepository->ophogen($civisaldo->uid, $inleg);
					$civisaldo->saldo += $inleg;
					$civisaldo->laatst_veranderd = date_create_immutable();
				});

				return $this->tableData([$civisaldo]);
			} else {
				return $form;
			}
		}

		throw new CsrToegangException();
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		$removed = array();
		foreach ($selection as $uuid) {
			/** @var CiviSaldo $civisaldo */
			$civisaldo = $this->civiSaldoRepository->retrieveByUUID($uuid);

			if ($civisaldo) {
				$civisaldo->deleted = true;
				$removed[] = new RemoveDataTableEntry($civisaldo->id, CiviSaldo::class);
				$this->civiSaldoRepository->update($civisaldo);
			}
		}

		if (!empty($removed)) {
			return $this->tableData($removed);
		}

		throw new CsrToegangException();
	}

	public function registreren() {
		$form = new LidRegistratieForm(new CiviSaldo());

		if ($form->validate()) {
			/** @var CiviSaldo $saldo */
			$saldo = $form->getModel();
			$saldo->laatst_veranderd = date_create_immutable();

			if (is_null($saldo->uid)) {
				$laatsteSaldo = $this->civiSaldoRepository->findLaatsteCommissie();
				$saldo->uid = ++$laatsteSaldo->uid;
			}

			if (is_null($saldo->naam)) {
				$saldo->naam = '';
			}

			if (count($this->civiSaldoRepository->findBy(['uid' => $saldo->uid])) === 1) {
				throw new CsrToegangException();
			} else {
				$this->civiSaldoRepository->create($saldo);
			}

			return $this->tableData([$saldo]);
		}

		return $form;
	}

	public function som() {
		$momentString = filter_input(INPUT_POST, 'moment', FILTER_SANITIZE_STRING);
		$moment = DateTime::createFromFormat("Y-m-d H:i:s", $momentString);
		if (!$moment) {
			throw new CsrToegangException();
		}

		return view('fiscaat.saldisom', [
			'saldisomform' => new SaldiSomForm($this->civiSaldoRepository, $moment),
			'saldisom' => $this->civiSaldoRepository->getSomSaldiOp($moment),
			'saldisomleden' => $this->civiSaldoRepository->getSomSaldiOp($moment, true),
		]);
	}

	public function zoek(Request $request) {
		$zoekterm = $request->query->get('q');

		$leden = $this->profielService->zoekLeden($zoekterm, 'naam', 'alle', 'achternaam');
		$uids = array_map(function ($profiel) { return $profiel->uid; }, $leden);

		$civiSaldi = $this->civiSaldoRepository->zoeken($uids, $zoekterm);

		$resp = [];
		foreach ($civiSaldi as $civiSaldo) {
			$profiel = ProfielRepository::get($civiSaldo->uid);
			$resp[] = [
				'label' => $profiel === false ? $civiSaldo->naam : $profiel->getNaam('volledig'),
				'value' => $civiSaldo->uid
			];
		}

		return new JsonResponse($resp);
	}
}
