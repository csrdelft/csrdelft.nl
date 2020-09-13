<?php


namespace CsrDelft\Twig;


use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ForumTwigExtension extends AbstractExtension {
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;

	public function __construct(ForumDradenVerbergenRepository $forumDradenVerbergenRepository, ForumPostsRepository $forumPostsRepository, ForumDradenRepository $forumDradenRepository) {
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDradenRepository = $forumDradenRepository;
	}

	public function getFunctions() {
		return [
			new TwigFunction('getAantalVerborgenVoorLid', [$this, 'getAantalVerborgenVoorLid']),
			new TwigFunction('getAantalWachtOpGoedkeuring', [$this, 'getAantalWachtOpGoedkeuring']),
			new TwigFunction('sliding_pager', 'sliding_pager', ['is_safe' => ['html']]),
			new TwigFunction('getHuidigePagina', [$this, 'getHuidigePagina']),
			new TwigFunction('getAantalPaginas', [$this, 'getAantalPaginas']),
			new TwigFunction('getBelangrijkOpties', [$this, 'getBelangrijkOpties']),
			new TwigFunction('draadGetAantalPaginas', [$this, 'draadGetAantalPaginas']),
			new TwigFunction('draadGetHuidigePagina', [$this, 'draadGetHuidigePagina']),
		];
	}

	public function getFilters() {
		return [
			new TwigFilter('highlight_zoekterm', [$this, 'highlight_zoekterm'], ['is_safe' => ['html']]),
			new TwigFilter('split_on_keyword', 'split_on_keyword', ['is_safe' => ['html']]),
		];
	}

	public function getBelangrijkOpties() {
		return ForumDradenRepository::$belangrijk_opties;
	}

	public function getAantalVerborgenVoorLid() {
		return $this->forumDradenVerbergenRepository->getAantalVerborgenVoorLid();
	}

	public function getAantalWachtOpGoedkeuring() {
		return $this->forumPostsRepository->getAantalWachtOpGoedkeuring();
	}

	public function getHuidigePagina() {
		return $this->forumDradenRepository->getHuidigePagina();
	}

	public function getAantalPaginas($forum_id = null) {
		return $this->forumDradenRepository->getAantalPaginas($forum_id);
	}

	public function draadGetAantalPaginas($draad_id) {
		return $this->forumPostsRepository->getAantalPaginas($draad_id);
	}

	public function draadGetHuidigePagina() {
		return $this->forumPostsRepository->getHuidigePagina();
	}

	function highlight_zoekterm($bericht, $zoekterm, $before = null, $after = null) {
		$before = $before ?: '<span style="background-color: rgba(255,255,0,0.4);">';
		$after = $after ?: '</span>';
		return preg_replace('/' . preg_quote($zoekterm, '/') . '/i', $before . '$0' . $after, $bericht);
	}

}
