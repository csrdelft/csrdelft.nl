<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\Component\DataTable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use CsrDelft\repository\fiscaat\CiviPrijsRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\fiscaat\producten\CiviProductForm;
use CsrDelft\view\fiscaat\producten\CiviProductSuggestiesResponse;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviProductenController extends AbstractController
{
	public function __construct(
		private readonly CiviProductRepository $civiProductRepository,
		private readonly CiviBestellingInhoudRepository $civiBestellingInhoudRepository,
		private readonly CiviPrijsRepository $civiPrijsRepository,
		private readonly EntityManagerInterface $em
	) {
	}

	/**
	 * @param Request $request
	 * @return CiviProductSuggestiesResponse
	 * @Auth(P_FISCAAT_READ)
	 */
	#[Route(path: '/fiscaat/producten/suggesties', methods: ['GET'])]
	public function suggesties(Request $request)
	{
		return new CiviProductSuggestiesResponse(
			$this->civiProductRepository->getSuggesties(
				SqlUtil::sql_contains($request->query->get('q'))
			)
		);
	}

	/**
	 * @return Response
	 * @Auth(P_FISCAAT_READ)
	 */
	#[Route(path: '/fiscaat/producten', methods: ['GET'])]
	public function overzicht()
	{
		return $this->render('fiscaat/pagina.html.twig', [
			'titel' => 'Producten beheer',
			'view' => new CiviProductTable(),
		]);
	}

	/**
	 * @return CiviProductForm
	 * @Auth(P_FISCAAT_MOD)
	 */
	#[Route(path: '/fiscaat/producten/bewerken', methods: ['POST'])]
	public function bewerken()
	{
		$selection = $this->getDataTableSelection();

		if (empty($selection)) {
			return new CiviProductForm(new CiviProduct());
		}

		/** @var CiviProduct $product */
		$product = $this->civiProductRepository->retrieveByUUID($selection[0]);
		$product->tmpPrijs = $product->getPrijs()->prijs;
		return new CiviProductForm($product);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_FISCAAT_MOD)
	 */
	#[Route(path: '/fiscaat/producten/verwijderen', methods: ['POST'])]
	public function verwijderen()
	{
		$selection = $this->getDataTableSelection();

		$removed = $this->em->transactional(function () use ($selection) {
			$removed = [];
			foreach ($selection as $uuid) {
				/** @var CiviProduct $product */
				$product = $this->civiProductRepository->retrieveByUUID($uuid);

				if ($product) {
					if (
						count(
							$this->civiBestellingInhoudRepository->findBy([
								'product_id' => $product->id,
							])
						) == 0
					) {
						$this->civiPrijsRepository->verwijderVoorProduct($product);
						$removed[] = new RemoveDataTableEntry(
							$product->id,
							CiviProduct::class
						);
						$this->em->remove($product);
						$this->em->flush();
					} else {
						throw new CsrGebruikerException(
							'Mag product niet verwijderen, het is al eens besteld'
						);
					}
				}
			}

			return $removed;
		});

		if (empty($removed)) {
			throw new CsrGebruikerException('Geen product verwijderd');
		}

		return $this->tableData($removed);
	}

	/**
	 * @param Request $request
	 * @return GenericDataTableResponse|CiviProductForm
	 * @Auth(P_FISCAAT_MOD)
	 */
	#[Route(path: '/fiscaat/producten/opslaan', methods: ['POST'])]
	public function opslaan(Request $request)
	{
		$id = $request->request->getInt('id');

		if (!$id) {
			$product = new CiviProduct();
		} else {
			$product = $this->civiProductRepository->getProduct($id);
		}

		$form = new CiviProductForm($product);

		if ($form->isPosted() && $form->validate()) {
			if ($product->id) {
				$this->civiProductRepository->update($product);
			} else {
				$this->civiProductRepository->create($product);
			}

			return $this->tableData([$product]);
		}

		return $form;
	}

	/**
	 * @return GenericDataTableResponse
	 * @Auth(P_FISCAAT_READ)
	 */
	#[
		Route(
			path: '/fiscaat/producten/{cie}',
			defaults: ['cie' => null],
			methods: ['POST']
		)
	]
	public function lijst($cie)
	{
		if ($cie) {
			return $this->tableData($this->civiProductRepository->findByCie($cie));
		}
		return $this->tableData($this->civiProductRepository->findAll());
	}
}
