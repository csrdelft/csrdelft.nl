<?php

namespace CsrDelft\controller;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Annotation\CsrfUnsafe;
use CsrDelft\common\FlashType;
use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\entity\CmsPagina;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van cms paginas.
 */
class CmsPaginaController extends AbstractController
{
	public function __construct(
		private readonly CmsPaginaRepository $cmsPaginaRepository
	) {
	}

	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/pagina')]
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
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/pagina/{naam}')]
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
		if (!$this->mag(P_LOGGED_IN)) {
			// Nieuwe layout altijd voor uitgelogde bezoekers
			if ($pagina->naam === 'thuis') {
				return $this->render('extern/index.html.twig', [
					'titel' => $pagina->titel,
				]);
			} elseif ($naam === 'vereniging') {
				return $this->render('extern/content.html.twig', [
					'pagina' => $pagina,
				]);
			} elseif ($naam === 'lidworden') {
				return $this->render('extern/owee.html.twig');
			}

			return $this->render('extern/content.html.twig', [
				'pagina' => $pagina,
			]);
		} else {
			// Nieuwe layout ook voor ingelogde bezoekers
			if ($pagina->naam === 'thuis') {
				return $this->render('voorpagina.html.twig', []);
			}

			return $this->render('cms/pagina.html.twig', ['pagina' => $pagina]);
		}
	}

	/**
	 * @param Request $request
	 * @param $naam
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 * @CsrfUnsafe
	 */
	#[Route(path: '/pagina/bewerken/{naam}')]
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
			$this->addFlash(FlashType::SUCCESS, 'Bijgewerkt: ' . $pagina->naam);
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
	 * @Auth(P_ADMIN)
	 */
	#[Route(path: '/pagina/verwijderen/{naam}', methods: ['POST'])]
	public function verwijderen($naam): JsonResponse
	{
		/** @var CmsPagina $pagina */
		$pagina = $this->cmsPaginaRepository->find($naam);
		$this->denyAccessUnlessGranted(CmsPaginaVoter::VERWIJDEREN, $pagina);

		$manager = $this->getDoctrine()->getManager();
		$manager->remove($pagina);
		$manager->flush();
		$this->addFlash(
			FlashType::SUCCESS,
			'Pagina ' . $naam . ' succesvol verwijderd'
		);

		return new JsonResponse($this->generateUrl('default')); // redirect
	}
}
