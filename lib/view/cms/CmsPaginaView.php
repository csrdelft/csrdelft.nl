<?php
namespace CsrDelft\view\cms;
use CsrDelft\Icon;
use CsrDelft\model\entity\CmsPagina;
use CsrDelft\view\CsrBB;
use CsrDelft\view\View;
use function CsrDelft\getMelding;

/**
 * CmsPaginaView.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken van een CmsPagina.
 */
class CmsPaginaView implements View {

	private $pagina;

	public function __construct(CmsPagina $pagina) {
		$this->pagina = $pagina;
	}

	function getModel() {
		return $this->pagina;
	}

	public function getBreadcrumbs() {
		return null;
	}

	function getTitel() {
		return $this->pagina->titel;
	}

	public function view() {
		echo getMelding();
		if ($this->pagina->magBewerken()) {
			echo '<a href="/pagina/bewerken/' . $this->pagina->naam . '" class="btn float-right"title="Bewerk pagina&#013;' . $this->pagina->laatst_gewijzigd . '">' . Icon::getTag('bewerken') . '</a>';
		}
		echo CsrBB::parseHtml(htmlspecialchars_decode($this->pagina->inhoud), $this->pagina->inline_html);
	}

}
