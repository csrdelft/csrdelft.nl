<?php

require_once 'MVC/view/WikiView.class.php';

/**
 * WikiController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de wiki.
 */
class WikiController extends Controller {

	public function __construct($query) {
		parent::__construct($query, preg_replace('/^\/wiki/', '/dokuwiki/', $query));
		$this->action = 'tonen';
	}

	public function mag($action) {
		return LoginModel::mag('P_LOGGED_IN');
	}

	public function tonen() {
		$body = new WikiView($this->model);
		$this->view = new CsrLayoutPage($body);
		$this->view->zijbalk = false;
	}

}
