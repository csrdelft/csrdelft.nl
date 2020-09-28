<?php

namespace CsrDelft\view\cms;

use CsrDelft\entity\CmsPagina;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Icon;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;

/**
 * CmsPaginaView.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken van een CmsPagina.
 */
class CmsPaginaView implements View, ToResponse {
	use ToHtmlResponse;
	private $pagina;

	public function __construct(CmsPagina $pagina) {
		$this->pagina = $pagina;
	}

	public function getModel() {
		return $this->pagina;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->pagina->titel;
	}

	public function view() {
		echo getMelding();
		if ($this->pagina->magBewerken()) {
			echo '<a href="/pagina/bewerken/' . $this->pagina->naam . '" class="btn float-right" title="Bewerk pagina&#013;' . $this->pagina->laatst_gewijzigd->format(DATETIME_FORMAT) . '">' . Icon::getTag('bewerken') . '</a>';
		}
		echo CsrBB::parseHtml(htmlspecialchars_decode($this->pagina->inhoud), $this->pagina->inline_html);
	}

}
