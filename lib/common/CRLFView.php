<?php


namespace CsrDelft\common;


use CsrDelft\view\View;

class CRLFView implements View {
	/**
	 * @var View
	 */
	private $view;

	public function __construct(View $view) {
		$this->view = $view;
	}

	public function view() {
		ob_start();
		$this->view->view();

		$ret = ob_get_clean();
		echo str_replace("\n", "\r\n", $ret);
	}

	public function getTitel() {
		return $this->view->getTitel();
	}

	public function getBreadcrumbs() {
		return $this->view->getBreadcrumbs();
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		return $this->view->getModel();
	}
}
