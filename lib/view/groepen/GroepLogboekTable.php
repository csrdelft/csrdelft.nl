<?php

namespace CsrDelft\view\groepen;

use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\view\datatable\DataTable;

class GroepLogboekTable extends DataTable {

	public function __construct(AbstractGroep $groep) {
		parent::__construct(ChangeLogEntry::class, $groep->getUrl() . '/logboek', false, 'moment');
		$this->hideColumn('subject');
		$this->searchColumn('property');
		$this->searchColumn('old_value');
		$this->searchColumn('new_value');
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Door');
	}

	public function getType() {
		return className($this);
	}

}
