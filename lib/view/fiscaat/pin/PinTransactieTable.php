<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\fiscaat\pin_transacties\PinTransactieModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieTable extends DataTable {
	public function __construct() {
		parent::__construct(PinTransactieModel::ORM, '/fiscaat/pin/overzicht', 'Overzicht van pintransacties');

		$this->setOrder(['datetime' => 'desc']);

		$this->addColumn('bestelling_id', null, 'Geen bestelling gevonden', null, null);

		$weergave = new DataTableKnop('', $this->dataTableId, '', '', 'Weergave', 'Weergave van de tabel', 'cart', 'collection');
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/pin/overzicht', '', 'Zonder besteling', 'Zonder bestelling weergeven', 'cart_error', 'sourceChange'));
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/pin/overzicht?filter=alles', '', 'Alles', 'Alles weergeven', 'cart_add', 'sourceChange'));
		$this->addKnop($weergave);
	}
}
