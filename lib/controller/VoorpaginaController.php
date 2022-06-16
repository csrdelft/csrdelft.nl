<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoorpaginaController extends AbstractController
{
	/**
	 * @param CmsPaginaRepository $cmsPaginaRepository
	 * @return Response
	 * @Route("/")
	 * @Auth(P_PUBLIC)
	 */
	public function voorpagina(CmsPaginaRepository $cmsPaginaRepository): Response
	{
		if ($this->isGranted(P_LOGGED_IN)) {
			return $this->render('voorpagina.html.twig', []);
		} else {
			return $this->render('extern/index.html.twig', [
				'titel' => (new CmsPaginaView(
					$cmsPaginaRepository->find('thuis')
				))->getTitel(),
			]);
		}
	}

	/**
	 * @param ForumDelenService $forumDelenService
	 * @param ForumPostsRepository $forumPostsRepository
	 * @return Response
	 * @Route("/voorpagina/forum")
	 */
	public function forum(
		ForumDelenService $forumDelenService,
		ForumPostsRepository $forumPostsRepository
	): Response {
		$belangrijk = true;

		return $this->render('voorpagina/forum.html.twig', [
			'draden' => $forumDelenService->getRecenteForumDraden(
				(int) lid_instelling('zijbalk', 'forum'),
				$belangrijk
			),
			'aantalWacht' => $forumPostsRepository->getAantalWachtOpGoedkeuring(),
			'belangrijk' => $belangrijk,
		]);
	}

	/**
	 * @param AgendaRepository $agendaRepository
	 * @return Response
	 * @Route("/voorpagina/agenda")
	 */
	public function agenda(AgendaRepository $agendaRepository): Response
	{
		// Agenda
		if (LoginService::mag(P_AGENDA_READ)) {
			$aantalWeken = lid_instelling('zijbalk', 'agendaweken');
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
				$key = date('Y-m-d', $item->getBeginMoment());
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
				'items' => $groups,
			]);
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param VerjaardagenService $verjaardagenService
	 * @return Response
	 * @Route("/voorpagina/verjaardagen")
	 */
	public function verjaardagen(
		VerjaardagenService $verjaardagenService
	): Response {
		// Komende verjaardagen
		if (LoginService::mag(P_LOGGED_IN)) {
			return $this->render('voorpagina/verjaardagen.html.twig', [
				'verjaardagen' => $verjaardagenService->getKomende(10),
				true,
			]);
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param FotoAlbumRepository $fotoAlbumRepository
	 * @return Response
	 * @Route("/voorpagina/fotoalbum")
	 */
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

		throw $this->createNotFoundException();
	}

	/**
	 * @param MaaltijdenRepository $maaltijdenRepository
	 * @return Response
	 * @Route("/voorpagina/maaltijden")
	 */
	public function maaltijden(MaaltijdenRepository $maaltijdenRepository): Response
	{
		$maaltijden = $maaltijdenRepository->getKomendeMaaltijdenVoorLid(
			LoginService::getUid()
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
	 * @Route("/voorpagina/posters")
	 */
	public function posters(): Response
	{
		return $this->render('voorpagina/posters.html.twig');
	}

	/**
	 * @return Response
	 * @Route("/voorpagina/civisaldo")
	 */
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
	 * @Route("/voorpagina/ishetal")
	 */
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
			lid_instelling('zijbalk', 'ishetal')
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
	 * @Route("/voorpagina/overig")
	 */
	public function overig()
	{
		return $this->render('voorpagina/overig.html.twig');
	}
}