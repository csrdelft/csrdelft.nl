<?php

/**
 * CmsPaginaView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bekijken van een CmsPagina.
 */
class CmsPaginaView extends SmartyTemplateView {

	public function __construct(CmsPagina $pagina) {
		parent::__construct($pagina);
	}

	function getTitel() {
		return $this->model->titel;
	}

	public function view() {
		echo SimpleHTML::getMelding();
		if ($this->model->magBewerken()) {
			echo '<a href="/pagina/bewerken/' . $this->model->naam . '" class="knop" style="float:right;" title="Bewerk pagina&#013;' . $this->model->laatst_gewijzigd . '">' . Icon::getTag('bewerken') . '</a>';
		}
		echo CsrHtmlUbb::parse(htmlspecialchars_decode($this->model->inhoud));
	}

}

/**
 * CmsPaginaForm.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bewerken van een CmsPagina.
 */
class CmsPaginaForm extends Formulier {

	function __construct(CmsPagina $pagina) {
		parent::__construct($pagina, 'cms-pagina-form', '/pagina/bewerken/' . $pagina->naam);

		$fields[] = new HtmlComment('<div><label>Laatst gewijzigd</label>' . reldate($pagina->laatst_gewijzigd) . '</div>');
		$fields[] = new TextField('titel', $pagina->titel, 'Titel');
		if ($pagina->magRechtenWijzigen()) {
			$fields[] = new RechtenField('rechten_bekijken', $pagina->rechten_bekijken, 'Rechten bekijken');
			$fields[] = new RechtenField('rechten_bewerken', $pagina->rechten_bewerken, 'Rechten bewerken');
		} else {
			$fields[] = new HtmlComment('<div><label>Rechten bekijken</label>' . $pagina->rechten_bekijken .
					'</div><div style="clear:left;"><label>Rechten bewerken</label>' . $pagina->rechten_bewerken . '</div>');
		}
		$fields[] = new UbbPreviewField('inhoud', $pagina->inhoud, 'Inhoud');
		$fields[] = new FormButtons('/pagina/' . $pagina->naam);

		$this->addFields($fields);

		$this->model->laatst_gewijzigd = getDateTime();
	}

	function getTitel() {
		return 'Pagina bewerken: ' . $this->model->naam;
	}

	function view() {
		echo '<h1>' . $this->getTitel() . '</h1><br/>';
		echo parent::view();
	}

}

/**
 * CmsPaginaZijkolomView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van de lijst met CmsPaginas voor de zijkolom.
 */
class CmsPaginaZijkolomView implements View {

	private $paginas;

	public function __construct(CmsPaginaModel $model) {
		$this->paginas = $model->getAllePaginas();
	}

	public function getModel() {
		return $this->paginas;
	}

	public function view() {
		echo '<h1>Pagina\'s</h1>';
		foreach ($this->paginas as $pagina) {
			echo '<div class="item">';
			echo '<a href="/pagina/' . $pagina->naam . '" title="' . $pagina->naam . '" >' . $pagina->titel . '</a><br />';
			echo '</div>';
		}
	}

}
