<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviPrijsModel;
use CsrDelft\model\fiscaat\CiviProductModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\fiscaat\producten\CiviProductForm;
use CsrDelft\view\fiscaat\producten\CiviProductSuggestiesResponse;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\producten\CiviProductTableResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviProductenController extends AbstractController {
	/**
	 * @var CiviProductModel
	 */
	private $civiProductModel;
	/**
	 * @var CiviBestellingInhoudModel
	 */
	private $civiBestellingInhoudModel;
	/**
	 * @var CiviPrijsModel
	 */
	private $civiPrijsModel;

	public function __construct(CiviProductModel $civiProductModel, CiviBestellingInhoudModel $civiBestellingInhoudModel, CiviPrijsModel $civiPrijsModel) {
		$this->civiProductModel = $civiProductModel;
		$this->civiBestellingInhoudModel = $civiBestellingInhoudModel;
		$this->civiPrijsModel = $civiPrijsModel;
	}

	public function suggesties(Request $request) {
		$query = sql_contains($request->query->get('q'));
		return new CiviProductSuggestiesResponse($this->civiProductModel->find('beschrijving LIKE ?', [$query]));
	}

	public function lijst() {
		return new CiviProductTableResponse($this->civiProductModel->find());
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
		$product = $this->civiProductModel->retrieveByUUID($selection[0]);
		$product->prijs = $this->civiProductModel->getPrijs($product)->prijs;
		return new CiviProductForm($product);
	}

	public function verwijderen() {
		$selection = $this->getDataTableSelection();

		list($removed, $existingOrders) = Database::transaction(function () use ($selection) {
			$removed = array();
			$existingOrders = array();
			foreach ($selection as $uuid) {
				/** @var CiviProduct $product */
				$product = $this->civiProductModel->retrieveByUUID($uuid);

				if ($product) {
					if ($this->civiBestellingInhoudModel->count('product_id = ?', array($product->id)) == 0) {
						$this->civiPrijsModel->verwijderVoorProduct($product);
						$this->civiProductModel->delete($product);
						$removed[] = $product;
					} else {
						$existingOrders[] = $product;
					}
				}
			}

			return [$removed, $existingOrders];
		});

		if (!empty($removed)) {
			return new RemoveRowsResponse($removed);
		} elseif (!empty($existingOrders)) {
			throw new CsrGebruikerException('Mag product niet verwijderen, het is al eens besteld');
		}

		throw new CsrGebruikerException('Geen product verwijderd');
	}

	public function opslaan() {
		$product = new CiviProduct();
		$form = new CiviProductForm($product);

		if ($form->isPosted() && $form->validate()) {
			if ($product->id) {
				$this->civiProductModel->update($product);
			} else {
				$this->civiProductModel->create($product);
			}

			return new CiviProductTableResponse([$product]);
		}

		return $form;
	}
}
