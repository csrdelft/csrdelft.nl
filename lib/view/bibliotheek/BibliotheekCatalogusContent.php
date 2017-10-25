<?php

namespace CsrDelft\view\bibliotheek;

class BibliotheekCatalogusContent extends AbstractBibliotheekView {

	public function __construct() {
		parent::__construct(null, 'Bibliotheek | Catalogus');
	}

	public function view() {
		$this->smarty->display('bibliotheek/catalogus.tpl');
	}

}
