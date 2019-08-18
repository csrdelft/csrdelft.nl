<?php

namespace CsrDelft\view\login;

use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class RememberLoginTable extends DataTable {

	public function __construct() {
		parent::__construct(RememberLoginModel::ORM, '/session/rememberdata', 'Automatisch inloggen', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->hideColumn('token');
		$this->hideColumn('uid');
		$this->searchColumn('remember_since');
		$this->searchColumn('device_name');

		$create = new DataTableKnop(Multiplicity::Zero(), '/session/remember', 'Toevoegen', 'Automatisch inloggen vanaf dit apparaat', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop(Multiplicity::One(), '/session/remember', 'Naam wijzigen', 'Wijzig naam van apparaat', 'edit');
		$this->addKnop($update);

		$lock = new DataTableKnop(Multiplicity::Any(), '/session/lockip', '(Ont)Koppel IP', 'Alleen inloggen vanaf bepaald IP-adres', 'lock');
		$this->addKnop($lock);

		$delte = new DataTableKnop(Multiplicity::Any(), '/session/forget', 'Verwijderen', 'Stop automatische login voor dit apparaat', 'delete');
		$this->addKnop($delte);
	}

	public function getType() {
		return get_class($this);
	}

}
