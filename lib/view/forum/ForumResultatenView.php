<?php

namespace CsrDelft\view\forum;

class ForumResultatenView extends ForumView {

	public function __construct(
		array $draden,
		$query = null
	) {
		parent::__construct($draden);
		if ($query !== null) {
			//FIXME: verder zoeken $this->smarty->assign('query', $query);
			$this->titel = 'Zoekresultaten voor: "' . $query . '"';
		} else {
			$this->titel = 'Wacht op goedkeuring';
		}
	}

	public function view() {
		$this->smarty->assign('resultaten', $this->model);
		$this->smarty->display('forum/resultaten.tpl');
	}

}
