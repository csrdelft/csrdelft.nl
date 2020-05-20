<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use CsrDelft\repository\fiscaat\CiviPrijsRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\view\fiscaat\producten\CiviProductForm;
use CsrDelft\view\fiscaat\producten\CiviProductSuggestiesResponse;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviProductenController extends AbstractController {
	/**
	 * @var CiviProductRepository
	 */
	private $civiProductRepository;
	/**
	 * @var CiviBestellingInhoudRepository
	 */
	private $civiBestellingInhoudRepository;
	/**
	 * @var CiviPrijsRepository
	 */
	private $civiPrijsRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	public function __construct(
		CiviProductRepository $civiProductRepository,
		CiviBestellingInhoudRepository $civiBestellingInhoudRepository,
		CiviPrijsRepository $civiPrijsRepository,
		EntityManagerInterface $em
	) {
		$this->civiProductRepository = $civiProductRepository;
		$this->civiBestellingInhoudRepository = $civiBestellingInhoudRepository;
		$this->civiPrijsRepository = $civiPrijsRepository;
		$this->em = $em;
	}

	public function suggesties(Request $request) {
		return new CiviProductSuggestiesResponse($this->civiProductRepository->getSuggesties(sql_contains($request->query->get('q'))));
	}

	public function lijst() {
		return $this->tableData($this->civiProductRepository->findAll());
	}

	public function overzicht() {
		return view('fiscaat.pagina', [
			'titel' => 'Producten beheer',
			'view' => new CiviProductTable(),
		]);
	}

	public function bewerken() {
		$selection = $this->getDataTableSelection();

		if (empty($selection)) {
			return new CiviProductForm(new CiviProduct());
		}

		/** @var CiviProduct $product */
		$product = $this->civiProductRepository->retrieveByUUID($selection[0]);
		$product->tmpPrijs = $product->getPrijs()->prijs;
		return new CiviProductForm($product);
	}

	public function verwijderen() {
		$selection = $this->getDataTableSelection();

		$removed = $this->em->transactional(function () use ($selection) {
			$removed = array();
			foreach ($selection as $uuid) {
				/** @var CiviProduct $product */
				$product = $this->civiProductRepository->retrieveByUUID($uuid);

				if ($product) {
					if (count($this->civiBestellingInhoudRepository->findBy(['product_id' => $product->id])) == 0) {
						$this->civiPrijsRepository->verwijderVoorProduct($product);
						$removed[] = new RemoveDataTableEntry($product->id, CiviProduct::class);
						$this->em->remove($product);
						$this->em->flush();
					} else {
						throw new CsrGebruikerException('Mag product niet verwijderen, het is al eens besteld');
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

	public function opslaan(Request $request) {
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
}
