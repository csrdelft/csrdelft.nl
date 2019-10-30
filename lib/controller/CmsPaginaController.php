<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\CmsPagina;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\cms\CmsPaginaForm;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\cms\CmsPaginaZijbalkView;
use CsrDelft\view\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van cms paginas.
 */
class CmsPaginaController extends AbstractController {

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

	public function overzicht() {
		return view('cms.overzicht', [
			'paginas' => CmsPaginaModel::instance()->getAllePaginas(),
		]);
	}

	public function bekijken($naam, $subnaam = "") {
		if ($subnaam) {
			$naam = $subnaam;
		}
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaModel->get($naam);
		if (!$pagina) { // 404
			throw new NotFoundHttpException();
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
			} elseif ($naam === 'vereniging') {
				$menu = true;
			}
			return view('layout-extern.' . $tmpl, [
				'titel' => $body->getTitel(),
				'body' => $body,
				'showmenu' => $menu,
			]);
		} else {
			return view('default', ['content' => $body]);
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
			if ($this->cmsPaginaModel->exists($pagina)) {
				$this->cmsPaginaModel->update($pagina);
				setMelding('Bijgewerkt: ' . $pagina->naam, 1);
	 		} else {
				$this->cmsPaginaModel->create($pagina);
				setMelding('Ingevoegd: ' . $pagina->naam, 1);
			}
			return $this->redirectToRoute('cms-bekijken', ['naam' => $pagina->naam]);
		} else {
			return view('default', ['content' => $form]);
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
