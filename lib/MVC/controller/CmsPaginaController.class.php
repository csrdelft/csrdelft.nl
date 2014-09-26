<?php

require_once 'MVC/model/CmsPaginaModel.class.php';
require_once 'MVC/view/CmsPaginaView.class.php';

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

	protected function mag($action) {
		return true; // check permission on page itself
	}

	public function bekijken($naam) {
		$pagina = $this->model->getPagina($naam);
		if (!($pagina instanceof CmsPagina)) { // 404
			$pagina = $this->model->getPagina('thuis');
		}
		if (!$pagina->magBekijken()) { // 403
			$this->geentoegang();
		}
		$body = new CmsPaginaView($pagina);
		if (!LoginModel::mag('P_LOGGED_IN')) { // nieuwe layout altijd voor uitgelogde bezoekers
			$tmpl = 'content';
			$menu = '';
			if ($naam === 'lidworden') {
				$tmpl = 'lidworden';
			} elseif ($pagina->naam === 'thuis') {
				$tmpl = 'index';
			} elseif ($this->hasParam(1) AND $this->getParam(1) === 'vereniging') {
				$menu = 'Vereniging';
			}
			$this->view = new CsrLayout2Page($body, $tmpl, $menu);
		} else {
			$this->view = new CsrLayoutPage($body, $this->zijbalk);
		}
	}

	public function bewerken($naam) {
		$pagina = $this->model->getPagina($naam);
		if (!($pagina instanceof CmsPagina)) {
			$pagina = $this->model->newPagina($naam);
		}
		if (!$pagina->magBewerken()) {
			$this->geentoegang();
		}
		$form = new CmsPaginaForm($pagina); // fetches POST values itself
		if ($form->validate()) {
			$pagina->laatst_gewijzigd = getDateTime();
			$rowcount = $this->model->update($pagina);
			if ($rowcount > 0) {
				SimpleHTML::setMelding('Bijgewerkt', 1);
			} else {
				SimpleHTML::setMelding('Geen wijzigingen', 0);
			}
			redirect(CSR_ROOT . '/' . $pagina->naam);
		} else {
			$this->view = new CsrLayoutPage($form, $this->zijbalk);
		}
	}

	public function verwijderen($naam) {
		$pagina = $this->model->getPagina($naam);
		if (!$pagina->magVerwijderen()) {
			$this->geentoegang();
		}
		if ($this->model->delete($pagina)) {
			SimpleHTML::setMelding('Pagina ' . $naam . ' succesvol verwijderd', 1);
			redirect(CSR_ROOT);
		} else {
			SimpleHTML::setMelding('Verwijderen mislukt', -1);
			redirect(CSR_ROOT);
		}
	}

}
