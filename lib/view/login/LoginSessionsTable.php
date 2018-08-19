<?php

namespace CsrDelft\view\login;

use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\DataTable;

/**
 * LoginSessionsTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van login sessies en diverse formulieren.
 */
class LoginSessionsTable extends DataTable {

	public function __construct() {
		parent::__construct(LoginModel::ORM, '/loginsessionsdata', 'Sessiebeheer', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->hideColumn('uid');
		$this->searchColumn('login_moment');
		$this->searchColumn('user_agent');
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

}
