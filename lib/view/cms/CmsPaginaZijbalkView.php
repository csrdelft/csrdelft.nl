<?php

namespace CsrDelft\view\cms;

use CsrDelft\model\CmsPaginaModel;
use CsrDelft\view\View;

/**
 * CmsPaginaZijbalkView.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van de lijst met CmsPaginas voor de zijbalk.
 */
class CmsPaginaZijbalkView implements View {

	private $paginas;

	public function __construct(CmsPaginaModel $model) {
		$this->paginas = $model->getAllePaginas();
	}

	public function getModel() {
		return $this->paginas;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Pagina\'s';
	}

	public function view() {
		echo '<div class="zijbalk-kopje"><a href="/pagina/bewerken">' . $this->getTitel() . '</a></div>';
		foreach ($this->paginas as $pagina) {
			echo '<div class="item">';
			echo '<a href="/pagina/' . $pagina->naam . '" title="' . htmlspecialchars($pagina->naam) . '" >' . $pagina->titel . '</a><br />';
			echo '</div>';
		}
	}

}
