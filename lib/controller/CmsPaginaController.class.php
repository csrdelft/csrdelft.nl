<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\CmsPagina;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\cms\CmsPaginaForm;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\cms\CmsPaginaZijbalkView;
use CsrDelft\view\CsrLayoutOweePage;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van cms paginas.
 */
class CmsPaginaController {
	use QueryParamTrait;

	/**
	 * Lijst van pagina's om te bewerken in de zijbalk
	 * @var CmsPaginaZijbalkView[]
	 */
	private $zijbalk = array();
	/** @var CmsPaginaModel */
	private $cmsPaginaModel;

	public function __construct() {
		$this->cmsPaginaModel = CmsPaginaModel::instance();
	}

	public function bekijken($naam, $subnaam = "") {
		if ($subnaam) {
			$naam = $subnaam;
		}
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaModel->get($naam);
		if (!$pagina) { // 404
			$pagina = $this->cmsPaginaModel->get('thuis');
		}
		if (!$pagina->magBekijken()) { // 403
			throw new CsrToegangException();
		}
		$body = new CmsPaginaView($pagina);
		if (!LoginModel::mag(P_LOGGED_IN)) { // nieuwe layout altijd voor uitgelogde bezoekers
			$tmpl = 'content';
			$menu = false;
			if ($pagina->naam === 'thuis') {
				$tmpl = 'index';
			} elseif ($this->hasParam(1) AND $this->getParam(1) === 'vereniging') {
				$menu = true;
			}
			return new CsrLayoutOweePage($body, $tmpl, $menu);
		} else {
			return new CsrLayoutPage($body, $this->zijbalk);
		}
	}

	public function bewerken($naam) {
		$pagina = $this->cmsPaginaModel->get($naam);
		if (!$pagina) {
			$pagina = $this->cmsPaginaModel->nieuw($naam);
		}
		if (!$pagina->magBewerken()) {
			throw new CsrToegangException();
		}
		$form = new CmsPaginaForm($pagina); // fetches POST values itself
		if ($form->validate()) {
			$pagina->laatst_gewijzigd = getDateTime();
			$rowCount = $this->cmsPaginaModel->update($pagina);
			if ($rowCount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			redirect('/pagina/' . $pagina->naam);
		} else {
			return new CsrLayoutPage($form, $this->zijbalk);
		}
	}

	public function verwijderen($naam) {
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaModel->get($naam);
		if (!$pagina OR !$pagina->magVerwijderen()) {
			throw new CsrToegangException();
		}
		if ($this->cmsPaginaModel->delete($pagina)) {
			setMelding('Pagina ' . $naam . ' succesvol verwijderd', 1);
		} else {
			setMelding('Verwijderen mislukt', -1);
		}
		return new JsonResponse(CSR_ROOT); // redirect
	}

}
