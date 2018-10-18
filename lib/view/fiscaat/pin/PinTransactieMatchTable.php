<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\model\fiscaat\pin\PinTransactieMatchModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\CollectionDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\ConfirmDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\knop\SourceChangeDataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchTable extends DataTable {
	public function __construct() {
		parent::__construct(PinTransactieMatchModel::ORM, '/fiscaat/pin/overzicht?filter=metFout', 'Overzicht van pintransacties matches');

		$weergave = new CollectionDataTableKnop(Multiplicity::None(), 'Weergave', 'Weergave van de tabel', 'cart');
		$weergave->addKnop(new SourceChangeDataTableKnop('/fiscaat/pin/overzicht?filter=metFout', 'Met fouten', 'Fouten weergeven', 'cart_error'));
		$weergave->addKnop(new SourceChangeDataTableKnop('/fiscaat/pin/overzicht?filter=alles', 'Alles', 'Alles weergeven', 'cart'));
		$this->addKnop($weergave);

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/fiscaat/pin/verwerk',  'Verwerk', 'Dit probleem verwerken', 'cart_edit'));
		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/fiscaat/pin/ontkoppel', 'Ontkoppel', 'Ontkoppel bestelling en transactie', 'arrow_divide'));
		$this->addKnop(new DataTableKnop(Multiplicity::Two(), '/fiscaat/pin/koppel', 'Koppel', 'Koppel een bestelling en transactie', 'arrow_join'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/fiscaat/pin/info', 'Info', 'Bekijk informatie over de gekoppelde bestelling', 'magnifier'));
		$this->addKnop(new DataTableKnop(Multiplicity::Any(), '/fiscaat/pin/verwijder_transactie', 'Verwijder', 'Verwijder matches', 'delete'));
		$this->addKnop(new DataTableKnop(Multiplicity::None(), '/fiscaat/pin/heroverweeg', 'Heroverweeg', 'Controleer op veranderingen in andere systemen', 'cart_go'));

		$this->addColumn('moment');
		$this->addColumn('transactie');
		$this->addColumn('bestelling');

		$this->hideColumn('transactie_id');
		$this->hideColumn('bestelling_id');

		$this->setOrder(['moment' => 'desc']);

		$this->searchColumn('status');
		$this->searchColumn('moment');
		$this->searchColumn('transactie');
		$this->searchColumn('bestelling');
	}
}
