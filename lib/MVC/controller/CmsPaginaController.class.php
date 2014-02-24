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
	 * Data access model
	 * @var CmsPaginaModel
	 */
	private $model;
	private $zijkolom = array();

	public function __construct($query) {
		parent::__construct($query);
		$this->model = new CmsPaginaModel();
		$this->action = 'bekijken';
		$naam = 'thuis';

		if ($this->hasParam(3)) {
			if ($this->getParam(2) === 'bewerken') {
				$this->action = 'bewerken';
				$naam = $this->getParam(3);
				$this->zijkolom[] = new CmsPaginaZijkolomView($this->model);
			} else {
				$this->geentoegang();
			}
		} elseif ($this->hasParam(2)) {
			$naam = $this->getParam(2);
			if ($this->getParam(1) === 'pagina') {
				$this->zijkolom[] = new CmsPaginaZijkolomView($this->model);
			}
		} else {
			$naam = $this->getParam(1);
		}
		$this->performAction(array($naam));
	}

	protected function hasPermission() {
		return true; // check permission on page itself
	}

	public static function magRechtenWijzigen() {
		return LoginLid::instance()->hasPermission('P_ADMIN');
	}

	public static function magVerwijderen() {
		return LoginLid::instance()->hasPermission('P_ADMIN');
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

		$nieuw = array('thuis', 'contact', 'csrindeowee', 'vereniging', 'lidworden', 'geloof', 'vorming', 'filmpjes', 'gezelligheid', 'sport', 'vragen', 'officieel', 'societeit', 'ontspanning', 'interesse', 'interesseverzonden', 'accountaanvragen');
		if (in_array($naam, $nieuw) && !LoginLid::instance()->hasPermission('P_LOGGED_IN')) { // nieuwe layout alleen voor specifieke paginas en uitgelogde bezoekers
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
			$this->view = new CsrLayoutPage($body, $this->zijkolom);
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
		$form = new CmsPaginaFormView($pagina); // fetches POST values itself
		if ($this->isPosted() AND $form->validate()) {
			$rowcount = $this->model->update($pagina);
			if ($rowcount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			invokeRefresh(CSR_ROOT . $pagina->naam);
		} else {
			$this->view = new CsrLayoutPage($form, $this->zijkolom);
		}
	}

	public function verwijderen($naam) {
		$pagina = $this->model->getPagina($naam);
		if (!($pagina instanceof CmsPagina) OR !self::magVerwijderen()) {
			$this->geentoegang();
		}
		$this->model->delete($pagina);
		invokeRefresh(CSR_ROOT, 'Verwijderd', 1);
	}

}
