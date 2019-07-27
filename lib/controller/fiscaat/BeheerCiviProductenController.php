<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\controller\framework\QueryParamTrait;
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

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @property CiviProductModel $model
 */
class BeheerCiviProductenController {
	use QueryParamTrait;

	public function __construct() {
		$this->model = CiviProductModel::instance();
	}

	public function suggesties() {
		$query = sql_contains($this->getParam('q'));
		return new CiviProductSuggestiesResponse($this->model->find('beschrijving LIKE ?', [$query]));
	}

	public function lijst() {
		return new CiviProductTableResponse($this->model->find());
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
		$product = $this->model->retrieveByUUID($selection[0]);
		$product->prijs = $this->model->getPrijs($product)->prijs;
		return new CiviProductForm($product);
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		list($removed, $existingOrders) = Database::transaction(function () use ($selection) {
			$removed = array();
			$existingOrders = array();
			foreach ($selection as $uuid) {
				/** @var CiviProduct $product */
				$product = $this->model->retrieveByUUID($uuid);

				if ($product) {
					if (CiviBestellingInhoudModel::instance()->count('product_id = ?', array($product->id)) == 0) {
						CiviPrijsModel::instance()->verwijderVoorProduct($product);
						$this->model->delete($product);
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
				$this->model->update($product);
			} else {
				$this->model->create($product);
			}

			return new CiviProductTableResponse([$product]);
		}

		return $form;
	}
}
