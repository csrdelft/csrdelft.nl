<?php

namespace CsrDelft\controller;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\service\maalcie\MaaltijdenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoorpaginaController extends AbstractController
{
	/**
	 * @param ForumDelenService $forumDelenService
	 * @param ForumPostsRepository $forumPostsRepository
	 * @return Response
	 */
	#[Route(path: '/voorpagina/forum')]
	public function forum(
		ForumDelenService $forumDelenService,
		ForumPostsRepository $forumPostsRepository
	): Response {
		return $this->render('voorpagina/forum.html.twig', [
			'draden' => $forumDelenService->getRecenteForumDraden(
				(int) InstellingUtil::lid_instelling('zijbalk', 'forum'),
				null
			),
			'aantalWacht' => $forumPostsRepository->getAantalWachtOpGoedkeuring(),
		]);
	}

	/**
	 * @param AgendaRepository $agendaRepository
	 * @return Response
	 */
	#[Route(path: '/voorpagina/agenda')]
	public function agenda(AgendaRepository $agendaRepository): Response
	{
		// Agenda
		if (LoginService::mag(P_AGENDA_READ)) {
			$aantalWeken = InstellingUtil::lid_instelling('zijbalk', 'agendaweken');
			$items = $agendaRepository->getAllAgendeerbaar(
				date_create_immutable(),
				date_create_immutable('next saturday + ' . $aantalWeken . ' weeks'),
				false,
				true
			);
			// TODO: nog uit de instellingen halen
			// if (count($items) > lid_instelling('zijbalk', 'agenda_max')) {
			// 	$items = array_slice($items, 0, lid_instelling('zijbalk', 'agenda_max'));
			// }

			$groups = [];
			foreach ($items as $item) {
				$key = date('Y-m-d', $item->getBeginMoment()->getTimestamp());
				if (!isset($groups[$key])) {
					$groups[$key] = [
						'items' => [$item],
						'beginMoment' => $item->getBeginMoment(),
					];
				} else {
					$groups[$key]['items'][] = $item;
				}
			}

			return $this->render('voorpagina/agenda.html.twig', [
				'groups' => $groups,
			]);
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param VerjaardagenService $verjaardagenService
	 * @return Response
	 */
	#[Route(path: '/voorpagina/verjaardagen')]
	public function verjaardagen(
		VerjaardagenService $verjaardagenService
	): Response {
		if (!LoginService::mag(P_VERJAARDAGEN)) {
			throw $this->createAccessDeniedException();
		}

		// Komende verjaardagen
		return $this->render('voorpagina/verjaardagen.html.twig', [
			'verjaardagen' => $verjaardagenService->getKomende(10),
			true,
		]);
	}

	/**
	 * @param FotoAlbumRepository $fotoAlbumRepository
	 * @return Response
	 */
	#[Route(path: '/voorpagina/fotoalbum')]
	public function fotoalbum(FotoAlbumRepository $fotoAlbumRepository): Response
	{
		// Nieuwste fotoalbum
		$album = $fotoAlbumRepository->getMostRecentFotoAlbum();
		if ($album !== null) {
			return $this->render('voorpagina/fotoalbum.html.twig', [
				'album' => $album,
				'jaargang' => LichtingenRepository::getHuidigeJaargang(),
			]);
		}

		return new Response();
	}

	/**
	 * @param MaaltijdenRepository $maaltijdenRepository
	 * @return Response
	 */
	#[Route(path: '/voorpagina/maaltijden')]
	public function maaltijden(MaaltijdenService $maaltijdenService): Response
	{
		$maaltijden = $maaltijdenService->getKomendeMaaltijdenVoorLid(
			$this->getProfiel()
		);

		$maaltijd = reset($maaltijden);

		$aantal = sizeof($maaltijden);

		return $this->render('voorpagina/maaltijden.html.twig', [
			'maaltijden' => $maaltijden,
			'maaltijd' => $maaltijd,
			'aantal' => $aantal,
		]);
	}

	/**
	 * @return Response
	 */
	#[Route(path: '/voorpagina/posters')]
	public function posters(): Response
	{
		return $this->render('voorpagina/posters.html.twig');
	}

	/**
	 * @return Response
	 */
	#[Route(path: '/voorpagina/civisaldo')]
	public function civisaldo(): Response
	{
		return $this->render('voorpagina/civisaldo.html.twig');
	}

	/**
	 * @param LidInstellingenRepository $lidInstellingenRepository
	 * @param RequestStack $requestStack
	 * @param AgendaRepository $agendaRepository
	 * @param WoordVanDeDagRepository $woordVanDeDagRepository
	 * @return Response
	 */
	#[Route(path: '/voorpagina/ishetal')]
	public function ishetal(
		LidInstellingenRepository $lidInstellingenRepository,
		RequestStack $requestStack,
		AgendaRepository $agendaRepository,
		WoordVanDeDagRepository $woordVanDeDagRepository
	): Response {
		$isHetAlView = new IsHetAlView(
			$lidInstellingenRepository,
			$requestStack,
			$agendaRepository,
			$woordVanDeDagRepository,
			InstellingUtil::lid_instelling('zijbalk', 'ishetal')
		);
		return $isHetAlView->toResponse();

		// FIXME: dit weghalen?
		//		if (lid_instelling('zijbalk', 'ishetal') != 'niet weergeven') {
		//			return (new IsHetAlView($this->lidInstellingenRepository, $this->requestStack, $this->agendaRepository, $this->woordVanDeDagRepository, lid_instelling('zijbalk', 'ishetal')))->__toString();
		//		}
		//
		//		return null;
	}

	/**
	 * @return Response
	 */
	#[Route(path: '/voorpagina/overig')]
	public function overig()
	{
		return $this->render('voorpagina/overig.html.twig');
	}
}
