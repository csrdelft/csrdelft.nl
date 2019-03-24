<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
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
class BeheerCiviProductenController extends AclController {
	public function __construct($query) {
		parent::__construct($query, CiviProductModel::instance());

		if ($this->getMethod() == "POST") {
			$this->acl = [
				'overzicht' => P_FISCAAT_READ,
				'toevoegen' => P_FISCAAT_MOD,
				'bewerken' => P_FISCAAT_MOD,
				'opslaan' => P_FISCAAT_MOD,
				'verwijderen' => P_FISCAAT_MOD
			];
		} else {
			$this->acl = [
				'overzicht' => P_FISCAAT_READ,
				'suggesties' => P_FISCAAT_READ
			];
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';

		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		return parent::performAction($args);
	}

	public function GET_suggesties() {
		$query = sql_contains($this->getParam('q'));
		$this->view = new CiviProductSuggestiesResponse($this->model->find('beschrijving LIKE ?', array($query)));
	}

	public function POST_overzicht() {
		$this->view = new CiviProductTableResponse($this->model->find());
	}

	public function GET_overzicht() {
		$this->view = view('fiscaat.pagina', [
			'titel' => 'Producten beheer',
			'view' => new CiviProductTable(),
		]);
	}

	public function POST_toevoegen() {
		$form = new CiviProductForm(new CiviProduct(), 'opslaan/nieuw');

		if ($form->validate()) {
			$product = $form->getModel();

			if ($this->model->exists($product)) {
				$this->model->update($product);
			} else {
				$this->model->create($product);
			}

			$this->view = new CiviProductTableResponse(array($product));
			return;
		}

		$this->view = $form;
	}

	public function POST_bewerken() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);

		/** @var CiviProduct $product */
		$product = $this->model->retrieveByUUID($selection[0]);
		$product->prijs = $this->model->getPrijs($product)->prijs;
		$this->view = new CiviProductForm($product, 'opslaan');
	}

	public function POST_verwijderen() {
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
			$this->view = new RemoveRowsResponse($removed);
			return;
		} elseif (!empty($existingOrders)) {
			throw new CsrGebruikerException('Mag product niet verwijderen, het is al eens besteld');
		}

		throw new CsrGebruikerException('Geen product verwijderd');
	}

	public function POST_opslaan() {
		if ($this->hasParam(4) AND $this->getParam(4) == "nieuw") {
			$form = new CiviProductForm(new CiviProduct(), 'opslaan/nieuw');
			if ($form->validate()) {
				$product = $form->getModel();
				$this->model->create($product);

				$this->view = new CiviProductTableResponse(array($product));
				return;
			}
		} else {
			$form = new CiviProductForm(new CiviProduct(), 'opslaan');
			if ($form->validate()) {
				$product = $form->getModel();
				$this->model->update($product);

				$this->view = new CiviProductTableResponse(array($product));
				return;
			}
		}

		$this->view = $form;
	}
}
