<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\fiscaat\pin\PinTransactieModel;
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
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/fiscaat/pin/overzicht?filter=alles', '', 'Alles', 'Alles weergeven', 'cart', 'sourceChange'));
		$this->addKnop($weergave);

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/pin/nieuw', '', 'Nieuwe bestelling', 'Maak een nieuwe bestelling voor deze transactie', 'cart_add'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/pin/verwijder', '', 'Verwijder bestelling', 'Verwijder deze bestelling', 'cart_delete'));
	}
}
