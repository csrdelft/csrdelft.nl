<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\InleggenForm;
use CsrDelft\view\fiscaat\saldo\LidRegistratieForm;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * BeheerCiviSaldoController.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/04/2017
 */
class BeheerCiviSaldoController extends AbstractController
{
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

	public function __construct(
		CiviSaldoRepository $civiSaldoRepository,
		CiviBestellingRepository $civiBestellingRepository,
		ProfielService $profielService
	) {
		$this->profielService = $profielService;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
	}

	/**
	 * @return Response
	 * @Route("/fiscaat/saldo")
	 * @Auth(P_FISCAAT_READ)
	 * @throws ExceptionInterface
	 */
	public function overzicht(Request $request): Response
	{
		$table = $this->createDataTable(CiviSaldoTable::class);

		if ($request->getMethod() == 'POST') {
			return $table->createData(
				$this->civiSaldoRepository->findBy(['deleted' => false])
			);
		}

		return $this->render('fiscaat/pagina.html.twig', [
			'titel' => 'Saldo beheer',
			'view' => $table->createView(),
		]);
	}

	/**
	 * @param EntityManagerInterface $em
	 * @param string $uid
	 * @return GenericDataTableResponse|InleggenForm
	 * @Route("/fiscaat/saldo/inleggen/{uid}", defaults={"uid"=null}, methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function inleggen(EntityManagerInterface $em, $uid): GenericDataTableResponse|InleggenForm
	{
		if ($uid) {
			$civisaldo = $this->civiSaldoRepository->find($uid);
		} else {
			$selection = $this->getDataTableSelection();
			/** @var CiviSaldo $civisaldo */
			$civisaldo = $this->civiSaldoRepository->retrieveByUUID($selection[0]);
		}

		if ($civisaldo) {
			$form = new InleggenForm($civisaldo);
			$values = $form->getValues();
			if (
				$form->validate() &&
				$values['inleg'] !== 0 &&
				$values['saldo'] == $civisaldo->saldo
			) {
				$inleg = $values['inleg'];
				$em->transactional(function () use ($inleg, $civisaldo) {
					$bestelling = $this->civiBestellingRepository->vanBedragInCenten(
						$inleg,
						$civisaldo->uid
					);
					$this->civiBestellingRepository->create($bestelling);

					$this->civiSaldoRepository->ophogen($civisaldo->uid, $inleg);
					$civisaldo->laatst_veranderd = date_create_immutable();
				});

				return $this->tableData([$civisaldo]);
			} else {
				return $form;
			}
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @return GenericDataTableResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/fiscaat/saldo/verwijderen", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function verwijderen(): GenericDataTableResponse
	{
		$selection = $this->getDataTableSelection();

		$removed = [];
		foreach ($selection as $uuid) {
			/** @var CiviSaldo $civisaldo */
			$civisaldo = $this->civiSaldoRepository->retrieveByUUID($uuid);

			if ($civisaldo) {
				$civisaldo->deleted = true;
				$removed[] = new RemoveDataTableEntry(
					$civisaldo->uid,
					CiviSaldo::class
				);
				$this->civiSaldoRepository->update($civisaldo);
			}
		}

		if (!empty($removed)) {
			return $this->tableData($removed);
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @return GenericDataTableResponse|LidRegistratieForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/fiscaat/saldo/registreren", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function registreren(): GenericDataTableResponse|LidRegistratieForm
	{
		$form = new LidRegistratieForm(new CiviSaldo());

		if ($form->validate()) {
			/** @var CiviSaldo $saldo */
			$saldo = $form->getModel();
			$saldo->laatst_veranderd = date_create_immutable();

			if (is_null($saldo->uid)) {
				$laatsteSaldo = $this->civiSaldoRepository->findLaatsteCommissie();
				$saldo->uid = $laatsteSaldo->uid;
				++$saldo->uid;
			}

			if (is_null($saldo->naam)) {
				$saldo->naam = '';
			}

			if (
				count($this->civiSaldoRepository->findBy(['uid' => $saldo->uid])) === 1
			) {
				throw $this->createAccessDeniedException();
			} else {
				$this->civiSaldoRepository->create($saldo);
			}

			return $this->tableData([$saldo]);
		}

		return $form;
	}

	/**
	 * @return Response
	 * @Route("/fiscaat/saldo/som", methods={"POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function som(): Response
	{
		$momentString = filter_input(INPUT_POST, 'moment', FILTER_SANITIZE_STRING);
		$moment = DateTime::createFromFormat('Y-m-d H:i', $momentString);
		if (!$moment) {
			throw $this->createAccessDeniedException();
		}

		return $this->render('fiscaat/saldisom.html.twig', [
			'saldisomform' => new SaldiSomForm($this->civiSaldoRepository, $moment),
			'saldisom' => $this->civiSaldoRepository->getSomSaldiOp($moment),
			'saldisomleden' => $this->civiSaldoRepository->getSomSaldiOp(
				$moment,
				true
			),
		]);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/fiscaat/saldo/zoek", methods={"GET"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function zoek(Request $request): JsonResponse
	{
		$zoekterm = $request->query->get('q');

		$leden = $this->profielService->zoekLeden(
			$zoekterm,
			'naam',
			'alle',
			'achternaam'
		);
		$uids = array_map(function ($profiel) {
			return $profiel->uid;
		}, $leden);

		$civiSaldi = $this->civiSaldoRepository->zoeken($uids, $zoekterm);

		$resp = [];
		foreach ($civiSaldi as $civiSaldo) {
			$profiel = ProfielRepository::get($civiSaldo->uid);
			$resp[] = [
				'label' => !$profiel ? $civiSaldo->naam : $profiel->getNaam('volledig'),
				'value' => $civiSaldo->uid,
			];
		}

		return new JsonResponse($resp);
	}
}
