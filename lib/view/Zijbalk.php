<?php

namespace CsrDelft\view;

use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
class Zijbalk
{
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly Environment $twig,
		private readonly MenuItemRepository $menuItemRepository,
		private readonly ForumDradenRepository $forumDradenRepository,
		private readonly ForumDelenService $forumDelenService,
		private readonly AgendaRepository $agendaRepository,
		private readonly ForumPostsRepository $forumPostsRepository,
		private readonly FotoAlbumRepository $fotoAlbumRepository,
		private readonly VerjaardagenService $verjaardagenService,
		private readonly LidInstellingenRepository $lidInstellingenRepository,
		private readonly WoordVanDeDagRepository $woordVanDeDagRepository
	) {
	}

	/**
	 * @return string[]
	 */
	public function getZijbalk()
	{
		return ArrayUtil::array_filter_empty([
			$this->blockIsHetAl(),
			$this->blockLustrum(),
			$this->blockFavorieten(),
			$this->blockSponsors(),
			$this->blockAgenda(),
			$this->blockForumNieuwsteBelangrijkBerichten(),
			$this->blockForumNieuwsteBerichten(),
			$this->blockForumZelfgepost(),
			$this->blockNieuwsteFotoAlbum(),
			$this->blockKomendeVerjaardagen(),
		]);
	}

	private function blockLustrum()
	{
		return $this->twig->render('menu/lustrumblock.html.twig');
	}

	private function blockIsHetAl()
	{
		// Is het al...
		if (
			InstellingUtil::lid_instelling('zijbalk', 'ishetal') != 'niet weergeven'
		) {
			return (new IsHetAlView(
				$this->lidInstellingenRepository,
				$this->requestStack,
				$this->agendaRepository,
				$this->woordVanDeDagRepository,
				InstellingUtil::lid_instelling('zijbalk', 'ishetal')
			))->__toString();
		}

		return null;
	}

	private function blockFavorieten()
	{
		// Favorieten menu
		if (
			LoginService::mag(P_LOGGED_IN) &&
			InstellingUtil::lid_instelling('zijbalk', 'favorieten') == 'ja'
		) {
			$menu = $this->menuItemRepository->getMenu(LoginService::getUid());
			$menu->tekst = 'Favorieten';
			return $this->twig->render('menu/block.html.twig', ['root' => $menu]);
		}

		return null;
	}

	private function blockSponsors()
	{
		// Sponsors
		if (LoginService::mag(P_LOGGED_IN)) {
			$sponsor_menu = $this->menuItemRepository->getMenu('sponsors');
			if ($sponsor_menu) {
				$sponsor_menu->tekst = 'Mogelijkheden';
				return $this->twig->render('menu/block.html.twig', [
					'root' => $sponsor_menu,
				]);
			}
		}

		return null;
	}

	private function blockAgenda()
	{
		// Agenda
		if (
			LoginService::mag(P_AGENDA_READ) &&
			InstellingUtil::lid_instelling('zijbalk', 'agendaweken') > 0 &&
			InstellingUtil::lid_instelling('zijbalk', 'agenda_max') > 0
		) {
			$aantalWeken = InstellingUtil::lid_instelling('zijbalk', 'agendaweken');
			$items = $this->agendaRepository->getAllAgendeerbaar(
				date_create_immutable(),
				date_create_immutable('next saturday + ' . $aantalWeken . ' weeks'),
				false,
				true
			);
			if (
				count($items) > InstellingUtil::lid_instelling('zijbalk', 'agenda_max')
			) {
				$items = array_slice(
					$items,
					0,
					InstellingUtil::lid_instelling('zijbalk', 'agenda_max')
				);
			}
			return $this->twig->render('agenda/zijbalk.html.twig', [
				'items' => $items,
			]);
		}

		return null;
	}

	private function blockForumNieuwsteBelangrijkBerichten()
	{
		// Nieuwste belangrijke forumberichten
		if (InstellingUtil::lid_instelling('zijbalk', 'forum_belangrijk') > 0) {
			return $this->twig->render('voorpagina.html.twig', [
				'draden' => $this->forumDelenService->getRecenteForumDraden(
					(int) InstellingUtil::lid_instelling('zijbalk', 'forum_belangrijk'),
					true
				),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true,
			]);
		}

		return null;
	}

	private function blockForumNieuwsteBerichten()
	{
		// Nieuwste forumberichten
		if (InstellingUtil::lid_instelling('zijbalk', 'forum') > 0) {
			$belangrijk =
				InstellingUtil::lid_instelling('zijbalk', 'forum_belangrijk') > 0
					? false
					: null;
			return $this->twig->render('voorpagina.html.twig', [
				'draden' => $this->forumDelenService->getRecenteForumDraden(
					(int) InstellingUtil::lid_instelling('zijbalk', 'forum'),
					$belangrijk
				),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk,
			]);
		}

		return null;
	}

	private function blockForumZelfgepost()
	{
		// Zelfgeposte forumberichten
		if (InstellingUtil::lid_instelling('zijbalk', 'forum_zelf') > 0) {
			$posts = $this->forumPostsRepository->getRecenteForumPostsVanLid(
				LoginService::getUid(),
				(int) InstellingUtil::lid_instelling('zijbalk', 'forum_zelf'),
				true
			);
			return $this->twig->render('forum/partial/post_zijbalk.html.twig', [
				'posts' => $posts,
			]);
		}

		return null;
	}

	private function blockNieuwsteFotoAlbum()
	{
		// Nieuwste fotoalbum
		if (InstellingUtil::lid_instelling('zijbalk', 'fotoalbum') == 'ja') {
			$album = $this->fotoAlbumRepository->getMostRecentFotoAlbum();
			if ($album !== null) {
				return $this->twig->render('voorpagina.html.twig', [
					'album' => $album,
					'jaargang' => LichtingenRepository::getHuidigeJaargang(),
				]);
			}
		}

		return null;
	}

	private function blockKomendeVerjaardagen()
	{
		// Komende verjaardagen
		if (
			LoginService::mag(P_LOGGED_IN) &&
			InstellingUtil::lid_instelling('zijbalk', 'verjaardagen') > 0
		) {
			return $this->twig->render('voorpagina.html.twig', [
				'verjaardagen' => $this->verjaardagenService->getKomende(
					(int) InstellingUtil::lid_instelling('zijbalk', 'verjaardagen')
				),
				'toonpasfotos' =>
					InstellingUtil::lid_instelling('zijbalk', 'verjaardagen_pasfotos') ==
					'ja',
			]);
		}

		return null;
	}
}
