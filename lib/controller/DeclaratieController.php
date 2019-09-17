<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\DeclaratieModel;
use CsrDelft\model\DeclaratieRegelModel;
use CsrDelft\model\entity\Declaratie;
use CsrDelft\model\entity\DeclaratieRegel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\view\declaratie\DeclaratieFormulier;
use CsrDelft\view\declaratie\DeclaratieResponse;
use CsrDelft\view\declaratie\DeclaratieTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieController {
	use QueryParamTrait;

	private $model;
	/**
	 * @var DeclaratieRegelModel
	 */
	private $declaratieRegelModel;

	public function __construct() {
		$this->model = DeclaratieModel::instance();
		$this->declaratieRegelModel = DeclaratieRegelModel::instance();
	}

	public function overzicht() {
		return view('default', [
			'content' => new DeclaratieTable(),
		]);
	}

	public function data() {
		return new DeclaratieResponse($this->model->find());
	}

	public function nieuw() {
		$declaratie = new Declaratie();
		$declaratie->iban = LoginModel::getProfiel()->bankrekening;
		$declaratie->naam = LoginModel::getProfiel()->getNaam('volledig');
		$declaratie->email = LoginModel::getProfiel()->getPrimaryEmail();
		$declaratie->datum = getDateTime();
		$declaratieRegel = new DeclaratieRegel();
		$declaratieRegel->omschrijving = 'Een declaratieregel';
		$declaratie->declaratie_regels[] = $declaratieRegel;
		$declaratie->declaratie_regels[] = new DeclaratieRegel();

		$formulier = new DeclaratieFormulier($declaratie);

		if ($formulier->isPosted() && $formulier->validate()) {
			Database::transaction(function () use ($declaratie) {
				$id = $this->model->create($declaratie);

				foreach ($declaratie->declaratie_regels as $declaratie_regel) {
					$declaratie_regel->declaratie_id = $id;
					$this->declaratieRegelModel->create($declaratie_regel);
				}
			});
		} else {
			return view('default', [
				'content' => $formulier
			]);
		}
	}
}
