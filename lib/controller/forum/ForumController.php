<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use CsrDelft\view\forum\ForumZoekenForm;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\Icon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het forum.
 */
class ForumController extends AbstractController
{


	/**
	 * Recente draadjes laten zien in tabel.
	 *
	 * @param int|string $pagina
	 * @param string|null $belangrijk
	 * @return Response
	 * @Auth(P_PUBLIC)
	 */
	#[
		Route(
			path: '/forum/recent/{pagina<\d+>}',
			methods: ['GET'],
			defaults: ['pagina' => 1]
		)
	]
	#[
		Route(
			path: '/forum/recent/{pagina<\d+>}/belangrijk',
			methods: ['GET'],
			defaults: ['pagina' => 1]
		)
	]
	public function recent(
		RequestStack $requestStack,
		$pagina = 1,
		$belangrijk = null
	) {
		$this->forumDradenRepository->setHuidigePagina((int) $pagina, 0);
		$belangrijk = $belangrijk === 'belangrijk' || $pagina === 'belangrijk';
		$deel = $this->forumDelenService->getRecent($belangrijk);

		$aantalPaginas = $this->forumDradenRepository->getAantalPaginas(
			$deel->forum_id
		);

		if ($pagina > $aantalPaginas) {
			throw $this->createNotFoundException();
		}

		if ($this->getUser()) {
			$concept = $this->forumDradenReagerenRepository->getConcept($deel);
		} else {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		}
		return $this->render('forum/deel.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumDelenService->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' => $aantalPaginas > 1,
			'belangrijk' => $belangrijk ? '/belangrijk' : '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel(
				$deel
			),
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel(
				$deel
			),
		]);
	}

	/**
	 * @param ForumDraad $draad
	 *
	 * @return (false|string)[]
	 *
	 * @psalm-return array{url: string, icon: string, title: 'Dit onderwerp is door het bestuur aangemerkt als belangrijk'|'Dit onderwerp is gesloten, u kunt niet meer reageren'|'Dit onderwerp is plakkerig, het blijft bovenaan'|false, label: string, value: string}
	 */
	private function draadAutocompleteArray(ForumDraad $draad): array
	{
		$url = '/forum/onderwerp/' . $draad->draad_id;

		if (
			InstellingUtil::lid_instelling('forum', 'open_draad_op_pagina') ==
			'ongelezen'
		) {
			$url .= '#ongelezen';
		} elseif (
			InstellingUtil::lid_instelling('forum', 'open_draad_op_pagina') ==
			'laatste'
		) {
			$url .= '#reageren';
		}

		if ($draad->belangrijk) {
			$icon = Icon::getTag($draad->belangrijk);
			$title = 'Dit onderwerp is door het bestuur aangemerkt als belangrijk';
		} elseif ($draad->gesloten) {
			$icon = Icon::getTag('lock');
			$title = 'Dit onderwerp is gesloten, u kunt niet meer reageren';
		} elseif ($draad->plakkerig) {
			$icon = Icon::getTag('note');
			$title = 'Dit onderwerp is plakkerig, het blijft bovenaan';
		} else {
			$icon = Icon::getTag('forum');
			$title = false;
		}

		return [
			'url' => $url,
			'icon' => $icon,
			'title' => $title,
			'label' => $draad->deel->titel,
			'value' => $draad->titel,
		];
	}
}
