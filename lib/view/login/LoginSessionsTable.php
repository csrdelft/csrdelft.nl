<?php

namespace CsrDelft\view\login;

use CsrDelft\entity\security\LoginSession;
use CsrDelft\view\datatable\DataTable;

/**
 * LoginSessionsTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van login sessies en diverse formulieren.
 */
class LoginSessionsTable extends DataTable {

	public function __construct() {
		parent::__construct(LoginSession::class, '/session/sessionsdata', 'Sessiebeheer', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->hideColumn('uid');
		$this->searchColumn('login_moment');
		$this->searchColumn('user_agent');
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

}
