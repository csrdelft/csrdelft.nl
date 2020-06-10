<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrToegangException;
use CsrDelft\entity\CmsPagina;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaForm;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van cms paginas.
 */
class CmsPaginaController extends AbstractController {
	/** @var CmsPaginaRepository */
	private $cmsPaginaRepository;

	public function __construct(CmsPaginaRepository $cmsPaginaRepository) {
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/pagina")
	 * @Auth(P_LOGGED_IN)
	 */
	public function overzicht() {
		return view('cms.overzicht', [
			'paginas' => $this->cmsPaginaRepository->getAllePaginas(),
		]);
	}

	/**
	 * @param $naam
	 * @param string $subnaam
	 * @return TemplateView
	 * @Route("/pagina/{naam}")
	 * @Auth(P_PUBLIC)
	 */
	public function bekijken($naam, $subnaam = "") {
		$paginaNaam = $naam;
		if ($subnaam) {
			$paginaNaam = $subnaam;
		}
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaRepository->find($paginaNaam);
		if (!$pagina) { // 404
			throw new NotFoundHttpException();
		}
		if (!$pagina->magBekijken()) { // 403
			throw new CsrToegangException();
		}
		$body = new CmsPaginaView($pagina);
		if (!LoginService::mag(P_LOGGED_IN)) { // nieuwe layout altijd voor uitgelogde bezoekers
			$tmpl = 'content';
			$menu = false;
			if ($pagina->naam === 'thuis') {
				$tmpl = 'index';
			} elseif ($naam === 'vereniging') {
				$menu = true;
			} elseif ($naam === 'lidworden') {
				$tmpl = 'owee';
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

	/**
	 * @param $naam
	 * @return TemplateView|RedirectResponse
	 * @Route("/pagina/bewerken/{naam}")
	 * @Auth(P_LOGGED_IN)
	 */
	public function bewerken($naam) {
		$pagina = $this->cmsPaginaRepository->find($naam);
		if (!$pagina) {
			$pagina = $this->cmsPaginaRepository->nieuw($naam);
		}
		if (!$pagina->magBewerken()) {
			throw new CsrToegangException();
		}
		$form = new CmsPaginaForm($pagina); // fetches POST values itself
		if ($form->validate()) {
			$pagina->laatst_gewijzigd = date_create_immutable();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($pagina);
			$manager->flush();
			setMelding('Bijgewerkt: ' . $pagina->naam, 1);
			return $this->redirectToRoute('csrdelft_cmspagina_bekijken', ['naam' => $pagina->naam]);
		} else {
			return view('default', ['content' => $form]);
		}
	}

	/**
	 * @param $naam
	 * @return JsonResponse
	 * @Route("/pagina/verwijderen/{naam}", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function verwijderen($naam) {
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaRepository->find($naam);
		if (!$pagina OR !$pagina->magVerwijderen()) {
			throw new CsrToegangException();
		}
		$manager = $this->getDoctrine()->getManager();
		$manager->remove($pagina);
		$manager->flush();
		setMelding('Pagina ' . $naam . ' succesvol verwijderd', 1);

		return new JsonResponse(CSR_ROOT); // redirect
	}

}
