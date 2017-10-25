<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/04/2017
 */
class CiviSaldoTable extends DataTable {
	public function __construct() {
		parent::__construct(CiviSaldo::class, '/fiscaat/saldo', 'Saldobeheer');

		$this->addColumn('naam', 'saldo');
		$this->addColumn('lichting', 'saldo');
		$this->addColumn('saldo', null, null, 'prijs_render', 'saldo', 'num-fmt');
		$this->setOrder(array('saldo' => 'asc'));

		$this->searchColumn('naam');

		$this->addKnop(new DataTableKnop('== 0', $this->dataTableId, '/fiscaat/saldo/registreren', 'post', 'Registreren', 'Lid registreren', 'toevoegen'));
		$this->addKnop(new DataTableKnop('>= 1', $this->dataTableId, '/fiscaat/saldo/verwijderen', 'post', 'Verwijderen', 'Saldo van lid verwijderen', 'verwijderen', 'confirm'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/saldo/inleggen', 'post', 'Inleggen', 'Saldo van lid ophogen', 'coins_add'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Saldo</span>';
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;
	}
}


