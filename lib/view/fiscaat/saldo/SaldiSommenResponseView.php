<?php

namespace CsrDelft\view\fiscaat\saldo;


use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\SmartyTemplateView;
use DateTime;

/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 25/10/2017
 *
 * @property CiviSaldoModel $model Het model waarmee de som van de saldi berekend wordt.
 * @property DateTime $moment Het moment waarop de som van de saldi berekend wordt.
 *
 * De view die gebruikt wordt als antwoord op een saldisommen request op een bepaalde datum.
 */
class SaldiSommenResponseView extends SmartyTemplateView {

	private $moment;

	public function __construct(CiviSaldoModel $model, DateTime $moment) {
		parent::__construct($model);

		$this->moment = $moment;
	}

	function view() {
		$this->smarty->assign('saldisomform', (new SaldiSomForm($this->model, $this->moment))->getHtml());
		$this->smarty->assign('saldisom', $this->model->getSomSaldiOp($this->moment));
		$this->smarty->assign('saldisomleden', $this->model->getSomSaldiOp($this->moment, true));
		$this->smarty->display('fiscaat/saldisommen.tpl');
	}
}
