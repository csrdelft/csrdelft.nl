<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\entity\CmsPagina;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaType;
use CsrDelft\view\cms\CmsPaginaView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van cms paginas.
 */
class CmsPaginaController extends AbstractController
{
	/** @var CmsPaginaRepository */
	private $cmsPaginaRepository;

	public function __construct(CmsPaginaRepository $cmsPaginaRepository)
	{
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
	 * @return Response
	 * @Route("/pagina")
	 * @Auth(P_LOGGED_IN)
	 */
	public function overzicht(): Response
	{
		return $this->render('cms/overzicht.html.twig', [
			'paginas' => $this->cmsPaginaRepository->getAllePaginas(),
		]);
	}

	/**
	 * @param $naam
	 * @param string $subnaam
	 * @return Response
	 * @Route("/pagina/{naam}")
	 * @Auth(P_PUBLIC)
	 */
	public function bekijken($naam, $subnaam = ''): Response
	{
		$paginaNaam = $naam;
		if ($subnaam) {
			$paginaNaam = $subnaam;
		}
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaRepository->find($paginaNaam);
		if (!$pagina) {
			// 404
			throw new NotFoundHttpException();
		}
		$this->denyAccessUnlessGranted(CmsPaginaVoter::BEKIJKEN, $pagina);
		$body = new CmsPaginaView($pagina);
		if (!$this->mag(P_LOGGED_IN)) {
			// Nieuwe layout altijd voor uitgelogde bezoekers
			if ($pagina->naam === 'thuis') {
				return $this->render('extern/index.html.twig', [
					'titel' => $body->getTitel(),
				]);
			} elseif ($naam === 'vereniging') {
				return $this->render('extern/content.html.twig', [
					'titel' => $body->getTitel(),
					'body' => $body,
				]);
			} elseif ($naam === 'lidworden') {
				return $this->render('extern/owee.html.twig');
			}

			return $this->render('extern/content.html.twig', [
				'titel' => $body->getTitel(),
				'body' => $body,
			]);
		} else {
			// Nieuwe layout ook voor ingelogde bezoekers
			if ($pagina->naam === 'thuis') {
				return $this->render('voorpagina.html.twig', []);
			}

			return $this->render('cms/pagina.html.twig', ['body' => $body]);
		}
	}

	/**
	 * @param Request $request
	 * @param $naam
	 * @return Response
	 * @Route("/pagina/bewerken/{naam}")
	 * @Auth(P_LOGGED_IN)
	 * @CsrfUnsafe
	 */
	public function bewerken(Request $request, $naam): Response
	{
		$pagina = $this->cmsPaginaRepository->find($naam);
		if (!$pagina) {
			$pagina = $this->cmsPaginaRepository->nieuw($naam);
		}
		$this->denyAccessUnlessGranted(CmsPaginaVoter::BEWERKEN, $pagina);

		$form = $this->createForm(CmsPaginaType::class, $pagina, [
			'rechten_wijzigen' => $this->isGranted(
				CmsPaginaVoter::RECHTEN_WIJZIGEN,
				$pagina
			),
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$pagina->laatstGewijzigd = date_create_immutable();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($pagina);
			$manager->flush();
			setMelding('Bijgewerkt: ' . $pagina->naam, 1);
			return $this->redirectToRoute('csrdelft_cmspagina_bekijken', [
				'naam' => $pagina->naam,
			]);
		} else {
			return $this->render('default_form.html.twig', [
				'titel' => 'Pagina bewerken: ' . $pagina->naam,
				'form' => $form->createView(),
				'cancel_url' => $this->generateUrl('csrdelft_cmspagina_bekijken', [
					'naam' => $pagina->naam,
				]),
			]);
		}
	}

	/**
	 * @param $naam
	 * @return JsonResponse
	 * @Route("/pagina/verwijderen/{naam}", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function verwijderen($naam): JsonResponse
	{
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaRepository->find($naam);
		$this->denyAccessUnlessGranted(CmsPaginaVoter::VERWIJDEREN, $pagina);

		$manager = $this->getDoctrine()->getManager();
		$manager->remove($pagina);
		$manager->flush();
		setMelding('Pagina ' . $naam . ' succesvol verwijderd', 1);

		return new JsonResponse($this->generateUrl('default')); // redirect
	}
}
