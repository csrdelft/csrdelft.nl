<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\entity\documenten\Document;
use CsrDelft\model\entity\documenten\DocumentCategorie;

/**
 * Documenten voor een bepaalde categorie tonen.
 */
class DocumentCategorieContent extends DocumentenView {

	private $documenten;

	/**
	 * DocumentCategorieContent constructor.
	 *
	 * @param DocumentCategorie $categorie
	 * @param Document[] $documenten
	 */
	public function __construct(DocumentCategorie $categorie, $documenten) {
		parent::__construct($categorie, 'Documenten in categorie: ' . $categorie->naam);

		$this->documenten = $documenten;
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <span class="active">' . $this->model->naam . '</span>';
	}

	public function view() {
		$this->smarty->assign('categorie', $this->model);
		$this->smarty->assign('documenten', $this->documenten);
		$this->smarty->display('documenten/documentencategorie.tpl');
	}

}
