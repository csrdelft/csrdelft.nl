<?php

namespace CsrDelft\view\login;

use CsrDelft\entity\security\LoginSession;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;

/**
 * LoginSessionsTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van login sessies en diverse formulieren.
 */
class LoginSessionsTable extends DataTable {

	public function __construct() {
		parent::__construct(LoginSession::class, '/session/sessionsdata', 'Sessiebeheer');
		$this->settings['tableTools']['aButtons'] = array();
		$this->hideColumn('uid');
		$this->searchColumn('login_moment');
		$this->searchColumn('user_agent');
		$this->addColumn('lock_ip', null, null, CellRender::Check());

		$this->setOrder(['login_moment' => 'desc']);

		$this->addRowKnop(new DataTableRowKnop('/session/endsession/:session_hash', 'Log uit', 'door_in'));
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

}
