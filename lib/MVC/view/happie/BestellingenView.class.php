<?php

/**
 * BestellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle bestellingen om te beheren.
 * 
 */
class HappieBestellingenView extends DataTable {

	public function __construct() {
		parent::__construct(HappieBestellingenModel::orm, get_class($this), 2, false, 'Overzicht bestellingen');

		$knop = new DataTableToolbarKnop('>= 0', null, 'rowcount', 'Count', 'Count selected rows', null);
		$knop->onclick = "alert($('#" . $this->tableId . " tbody tr.selected').length + ' row(s) selected');";

		$toolbar = new DataTableToolbar();
		$toolbar->addKnop($knop);

		$fields[] = $toolbar;
		$this->addFields($fields);
	}

}
