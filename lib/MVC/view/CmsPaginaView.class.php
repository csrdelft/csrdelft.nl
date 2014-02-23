<?php

/**
 * CmsPaginaView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bekijken van een CmsPagina.
 */
class CmsPaginaView extends TemplateView {

	public function __construct(CmsPagina $pagina) {
		parent::__construct($pagina);
	}

	function getTitel() {
		return $this->model->titel;
	}

	public function view() {
		echo $this->getMelding();
		if ($this->model->magBewerken()) {
			echo '<a href="/pagina/' . $this->model->naam . '/bewerken" class="knop" style="float:right;" title="Bewerk pagina">' . Icon::getTag('bewerken') . '</a>';
		}
		echo CsrHtmlUbb::parse($this->model->inhoud);
	}

}

/**
 * CmsPaginaFormView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bewerken van een CmsPagina.
 */
class CmsPaginaFormView extends Formulier {

	private $actie;

	function __construct(CmsPagina $pagina, $actie) {
		parent::__construct($pagina, 'cms-pagina-form', '/pagina/' . $pagina->naam . '/' . $actie);
		$this->actie = $actie;

		$fields[] = new TextField('titel', $pagina->titel, 'Titel');
		if (CmsPaginaController::magRechtenWijzigen()) {
			$fields[] = new TextField('rechten_bekijken', $pagina->rechten_bekijken, 'Rechten bekijken');
			$fields[] = new TextField('rechten_bewerken', $pagina->rechten_bewerken, 'Rechten bewerken');
		}
		$fields[] = new UbbPreviewField('inhoud', $pagina->inhoud, 'Inhoud');
		$fields[] = new SubmitResetCancel('/pagina/' . $pagina->naam);

		$this->addFields($fields);

		$this->model->laatst_gewijzigd = date('Y-m-d H:i:s');
	}

	function getTitel() {
		return 'Pagina ' . $this->actie . ': ' . $this->model->naam;
	}

	function view() {
		echo '<h1>' . $this->getTitel() . '</h1>';
		if (!CmsPaginaController::magRechtenWijzigen()) {
			echo '<p>Deze pagina is zichtbaar voor: ' . LoginLid::formatPermissionstring($this->model->rechten_bekijken);
			echo ' en bewerkbaar voor: ' . LoginLid::formatPermissionstring($this->model->rechten_bewerken) . '.</p>';
		}
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
			echo '<a href="/pagina/' . mb_htmlentities($pagina->naam) . '/bewerken" title="' . mb_htmlentities($pagina->naam) . '" >' . mb_htmlentities($pagina->titel) . '</a><br />';
			echo '</div>';
		}
	}

}
