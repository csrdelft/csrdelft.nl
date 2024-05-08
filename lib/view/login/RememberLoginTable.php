<?php

namespace CsrDelft\view\login;

use CsrDelft\entity\security\RememberLogin;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

class RememberLoginTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(
			RememberLogin::class,
			'/session/rememberdata',
			'Automatisch inloggen'
		);
		$this->settings['tableTools']['aButtons'] = [];
		$this->deleteColumn('token');
		$this->deleteColumn('series');
		$this->hideColumn('uid');
		$this->searchColumn('remember_since');
		$this->searchColumn('device_name');
		$this->setOrder(['last_used' => 'desc']);

		$this->selectEnabled = false;

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				'/session/forget-all',
				'Alles verwijderen',
				'Automatisch inloggen van alle apparaten verwijderen',
				'verwijderen'
			)
		);
		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				'/session/remember',
				'Toevoegen',
				'Automatisch inloggen vanaf dit apparaat',
				'toevoegen'
			)
		);

		$this->addRowKnop(
			new DataTableRowKnop(
				'/session/remember',
				'Wijzig naam van apparaat',
				'bewerken'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				'/session/lockip',
				'(Ont)Koppel IP, alleen inloggen vanaf bepaald IP-adres',
				'slotje'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				'/session/forget',
				'Stop automatische login voor dit apparaat',
				'verwijderen'
			)
		);
	}

	public function getType()
	{
		return get_class($this);
	}
}
