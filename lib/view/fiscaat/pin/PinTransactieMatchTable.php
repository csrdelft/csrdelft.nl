<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\SourceChangeDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchTable extends DataTable {
	public function __construct() {
		parent::__construct(PinTransactieMatch::class, '/fiscaat/pin?filter=metFout', 'Overzicht van pintransacties matches');

		$weergave = new CollectionDataTableKnop(Multiplicity::None(), 'Weergave', 'Weergave van de tabel', 'cart');
		$weergave->addKnop(new SourceChangeDataTableKnop('/fiscaat/pin?filter=metFout', 'Met fouten', 'Fouten weergeven', 'cart_error'));
		$weergave->addKnop(new SourceChangeDataTableKnop('/fiscaat/pin?filter=alles', 'Alles', 'Alles weergeven', 'cart'));
		$this->addKnop($weergave);

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/fiscaat/pin/verwerk',  'Verwerk', 'Dit probleem verwerken', 'cart_edit'));
		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/fiscaat/pin/ontkoppel', 'Ontkoppel', 'Ontkoppel bestelling en transactie', 'arrow_divide'));
		$this->addKnop(new DataTableKnop(Multiplicity::Two(), '/fiscaat/pin/koppel', 'Koppel', 'Koppel een bestelling en transactie', 'arrow_join'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/fiscaat/pin/info', 'Info', 'Bekijk informatie over de gekoppelde bestelling', 'magnifier'));
		$this->addKnop(new DataTableKnop(Multiplicity::Any(), '/fiscaat/pin/negeer', 'Negeer', 'Negeer match(es)', 'delete'));
		$this->addKnop(new DataTableKnop(Multiplicity::None(), '/fiscaat/pin/heroverweeg', 'Heroverweeg', 'Controleer op veranderingen in andere systemen', 'cart_go'));

		$this->addColumn('moment');
		$this->addColumn('bestelling_moment');
		$this->addColumn('verschil');
		$this->addColumn('transactie');
		$this->addColumn('bestelling');

		$this->hideColumn('transactie_id');
		$this->hideColumn('bestelling_id');
		$this->hideColumn('notitie');

		$this->setOrder(['moment' => 'desc']);

		$this->searchColumn('status');
		$this->searchColumn('moment');
		$this->searchColumn('transactie');
		$this->searchColumn('bestelling');

		$this->vliegendeKnoppen = true;
	}
}
