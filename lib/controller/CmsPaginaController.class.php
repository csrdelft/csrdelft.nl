<?php

require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';

/**
 * CmsPaginaController.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de agenda.
 */
class CmsPaginaController extends Controller {

	/**
	 * Lijst van pagina's om te bewerken in de zijbalk
	 * @var CmsPaginaZijbalkView[]
	 */
	private $zijbalk = array();

	public function __construct($query) {
		parent::__construct($query, CmsPaginaModel::instance());
	}

	public function performAction(array $args = array()) {
		$this->action = 'bekijken';
		if ($this->hasParam(3) AND $this->getParam(2) === 'bewerken') {
			$this->action = 'bewerken';
			$naam = $this->getParam(3);
			$this->zijbalk[] = new CmsPaginaZijbalkView($this->model);
		} elseif ($this->hasParam(3) AND $this->getParam(2) === 'verwijderen') {
			$this->action = 'verwijderen';
			$naam = $this->getParam(3);
		} elseif ($this->hasParam(2)) {
			$naam = $this->getParam(2);
			if ($this->getParam(1) === 'pagina') {
				$this->zijbalk[] = new CmsPaginaZijbalkView($this->model);
			}
		} elseif ($this->hasParam(1)) {
			$naam = $this->getParam(1);
		} else {
			$naam = Instellingen::get('stek', 'homepage');
		}
		parent::performAction(array($naam));
	}

	protected function mag($action, array $args) {
		return true; // check permission on page itself
	}

	public function bekijken($naam) {
		// BEGIN example
		$pagina = new CmsPagina();
		$pagina->titel = 'Basic Example with JSX';
		$pagina->inline_html = true;
		$pagina->inhoud = <<<HTML
<h2 class="Titel">{$pagina->titel}</h2>
<div id="react_example">
  <p>
	To install React, follow the instructions on
	<a href="https://github.com/facebook/react/">GitHub</a>.
  </p>
  <p>
	If you can see this, React is <strong>not</strong> working right.
	If you checked out the source from GitHub make sure to run <code>grunt</code>.
  </p>
</div>
<h4>Example Details</h4>
<p>This is written with JSX and transformed in the browser.</p>
<p>
  Learn more about React at
  <a href="https://facebook.github.io/react" target="_blank">facebook.github.io/react</a>.
</p>
<script type="text/babel" src="/layout/jsx/react_example.js"></script>
HTML;
		$body = new CmsPaginaView($pagina);
		$this->view = new CsrLayoutPage($body, $this->zijbalk);
		$this->view->addCompressedResources('react');
		$this->view->addScript('https://unpkg.com/babel-core@5.8.38/browser.min.js', true);
		// END example
		return;

		$pagina = $this->model->get($naam);
		if (!$pagina) { // 404
			$pagina = $this->model->get('thuis');
		}
		if (!$pagina->magBekijken()) { // 403
			$this->geentoegang();
		}
		$body = new CmsPaginaView($pagina);
		if (!LoginModel::mag('P_LOGGED_IN')) { // nieuwe layout altijd voor uitgelogde bezoekers
			$tmpl = 'content';
			$menu = '';
			if ($pagina->naam === 'thuis') {
				$tmpl = 'index';
			} elseif ($this->hasParam(1) AND $this->getParam(1) === 'vereniging') {
				$menu = 'Vereniging';
			}
			$this->view = new CsrLayoutOweePage($body, $tmpl, $menu);
		} else {
			$this->view = new CsrLayoutPage($body, $this->zijbalk);
			if ($pagina->naam === 'thuis') {
				$this->view->addCompressedResources('fotoalbum');
			}
		}
	}

	public function bewerken($naam) {
		$pagina = $this->model->get($naam);
		if (!$pagina) {
			$pagina = $this->model->nieuw($naam);
		}
		if (!$pagina->magBewerken()) {
			$this->geentoegang();
		}
		$form = new CmsPaginaForm($pagina); // fetches POST values itself
		if ($form->validate()) {
			$pagina->laatst_gewijzigd = getDateTime();
			$rowCount = $this->model->update($pagina);
			if ($rowCount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			redirect('/pagina/' . $pagina->naam);
		} else {
			$this->view = new CsrLayoutPage($form, $this->zijbalk);
		}
	}

	public function verwijderen($naam) {
		$pagina = $this->model->get($naam);
		if (!$pagina OR ! $pagina->magVerwijderen()) {
			$this->geentoegang();
		}
		if ($this->model->delete($pagina)) {
			setMelding('Pagina ' . $naam . ' succesvol verwijderd', 1);
			redirect(CSR_ROOT);
		} else {
			setMelding('Verwijderen mislukt', -1);
			redirect(CSR_ROOT);
		}
	}

}
