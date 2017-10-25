<?php

namespace CsrDelft\view\login;

use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class RememberLoginTable extends DataTable {

	public function __construct() {
		parent::__construct(RememberLoginModel::ORM, '/loginrememberdata', 'Automatisch inloggen', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->hideColumn('token');
		$this->hideColumn('uid');
		$this->searchColumn('remember_since');
		$this->searchColumn('device_name');

		$create = new DataTableKnop('== 0', $this->dataTableId, '/loginremember', 'post popup', 'Toevoegen', 'Automatisch inloggen vanaf dit apparaat', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->dataTableId, '/loginremember', 'post popup', 'Naam wijzigen', 'Wijzig naam van apparaat', 'edit');
		$this->addKnop($update);

		$lock = new DataTableKnop('>= 1', $this->dataTableId, '/loginlockip', 'post', '(Ont)Koppel IP', 'Alleen inloggen vanaf bepaald IP-adres', 'lock');
		$this->addKnop($lock);

		$delte = new DataTableKnop('>= 1', $this->dataTableId, '/loginforget', 'post', 'Verwijderen', 'Stop automatische login voor dit apparaat', 'delete');
		$this->addKnop($delte);
	}

	public function getType() {
		return get_class($this);
	}

}
