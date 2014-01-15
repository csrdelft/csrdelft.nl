<?php

/*
 * PeilingContent
 * 
 * Roept template aan.
 */
require_once 'peiling.class.php';

class PeilingContent extends TemplateView {

	private $peiling;

	function PeilingContent(Peiling $peiling) {
		parent::__construct();
		$this->peiling = $peiling;
	}

	public function getHTML($beheer = false) {


		$this->smarty->assign('peiling', $this->peiling);
		$this->smarty->assign('beheer', $beheer);

		return $this->smarty->fetch('peiling.ubb.tpl');
	}

	public function view() {
		echo $this->getHTML();
	}

}

?>
