<?php


namespace CsrDelft\Twig\Extension;


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
			new TwigFunction('sliding_pager', [$this, 'sliding_pager'], ['is_safe' => ['html']]),
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

	public function highlight_zoekterm($bericht, $zoekterm, $before = null, $after = null) {
		$before = $before ?: '<span style="background-color: rgba(255,255,0,0.4);">';
		$after = $after ?: '</span>';
		return preg_replace('/' . preg_quote($zoekterm, '/') . '/i', $before . '$0' . $after, $bericht);
	}

	/**
	 * Gebasseerd op de sliding_pager smarty plugin
	 * -------------------------------------------------------------
	 * Type:     function
	 * Name:     sliding_page
	 * Purpose:  create a sliding-pager for page browsing
	 * Version:  0.1.1
	 * Date:     April 11, 2004
	 * Last Modified:    March 31, 2014
	 * Author:   Mario Witte <mario dot witte at chengfu dot net>
	 * HTTP:     http://www.chengfu.net/
	 * -------------------------------------------------------------
	 * @param array $params
	 * @return string
	 */
	public function sliding_pager($params)
	{
		/*
			@param  mixed   $pagecount          - number of pages to browse
			@param  int     $linknum            - max. number of links to show on one page (default: 5)
			@param  int     $curpage            - current page number
			@param  string  baseurl             - baseurl to which the pagenumber will appended
			@param  string  urlAppend          - text to append to url after pagenumber, e.g. "html" (default: "")
			@param  string  txtPre             - laat zien voor de paginering
			@param  string  txtFirst           - text for link to first page (default: "<<")
			@param  string  txtPrev            - text for link to previous page (default: "<")
			@param  string  separator           - text to print between page numbers (default: " ")
			@param  string  txtNext            - text for link to next page (default: ">")
			@param  string  txtLast            - text for link to last page (default: ">>")
			@param  string  txtPost            - laat zien na de paginering
			@param  string  txtSkip            - text shown when page s are skipped (not shown) (default: "…")
			@param  string  cssClass           - css class for the pager (default: "")
			@param  boolean linkCurrent        - whether to link the current page (default: false)
			@param  boolean showAlways         - als er maar 1 pagina is, toch laten zien
			@param  boolean showFirstLast     - eerste/laatste links laten zien
			@param  boolean showPrevNext      - vorige/volgende links laten zien
		 */

		/* Define all vars with default value */
		$pagecount = 0;
		$curpage = 0;
		$baseurl = '';
		$linknum = 5;
		$urlAppend = '';
		$txtPrev = '<';
		$separator = ' ';
		$txtNext = '>';
		$txtSkip = '…';
		$cssClass = '';
		$showPrevNext = false;

		/* Import parameters */
		extract($params);

		/* Convert page count if array */
		if (is_array($pagecount)) {
			$pagecount = sizeof($pagecount);
		}

		/* Define additional required vars */
		if ($linknum % 2 == 0) {
			$deltaL = ($linknum / 2) - 1;
			$deltaR = $linknum / 2;
		} else {
			$deltaL = $deltaR = ($linknum - 1) / 2;
		}

		/* There is no 0th page: assume last page */
		$curpage = $curpage == 0 ? $pagecount : $curpage;

		/* Internally we need an "array-compatible" index */
		$intCurpage = $curpage - 1;

		/* Paging needed? */
		if ($pagecount <= 1) {
			// No paging needed for one page
			return '';
		}

		/* Build all page links (we'll delete some later if required) */
		$links = array();
		for ($i = 0; $i < $pagecount; $i++) {
			$links[$i] = $i + 1;
		}

		/* Sliding needed? */
		if ($pagecount > $linknum) { // Yes
			if (($intCurpage - $deltaL) < 1) { // Delta_l needs adjustment, we are too far left
				$deltaL = $intCurpage - 1;
				$deltaR = $linknum - $deltaL - 1;
			}
			if (($intCurpage + $deltaR) > $pagecount) { // Delta_r needs adjustment, we are too far right
				$deltaR = $pagecount - $intCurpage;
				$deltaL = $linknum - $deltaR - 1;
			}
			if ($intCurpage - $deltaL > 1) { // Let's do some cutting on the left side
				array_splice($links, 0, $intCurpage - $deltaL);
			}
			if ($intCurpage + $deltaR < $pagecount) { // The right side will also need some treatment
				array_splice($links, $intCurpage + $deltaR + 2 - $links[0]);
			}
		}

		/* Build link bar */
		$retval = '';
		$cssClass = $cssClass ? 'class="' . $cssClass . '"' : '';
		if ($curpage > 1 && $showPrevNext) {
			$retval .= '<a href="' . $baseurl . ($curpage - 1) . $urlAppend . '" ' . $cssClass . '>' . $txtPrev . '</a>';
			$retval .= $separator;
		}
		if ($links[0] != 1) {
			$retval .= '<a href="' . $baseurl . '1' . $urlAppend . '" ' . $cssClass . '>1</a>';
			if ($links[0] == 2) {
				$retval .= $separator;
			} else {
				$retval .= $separator . $txtSkip . $separator;
			}
		}
		for ($i = 0; $i < sizeof($links); $i++) {
			if ($links[$i] != $curpage) {
				$retval .= '<a href="' . $baseurl . $links[$i] . $urlAppend . '" ' . $cssClass . '>' . $links[$i] . '</a>';
			} else {
				$retval .= '<span class="curpage">' . $links[$i] . '</span>';
			}

			if ($i < sizeof($links) - 1) {
				$retval .= $separator;
			}
		}
		if ($links[sizeof($links) - 1] != $pagecount) {
			if ($links[sizeof($links) - 2] != $pagecount - 1) {
				$retval .= $separator . $txtSkip . $separator;
			} else {
				$retval .= $separator;
			}
			$retval .= '<a href="' . $baseurl . $pagecount . $urlAppend . '" ' . $cssClass . '>' . $pagecount . '</a>';
		}
		if ($curpage != $pagecount && $showPrevNext) {
			$retval .= $separator;
			$retval .= '<a href="' . $baseurl . ($curpage + 1) . $urlAppend . '" ' . $cssClass . '>' . $txtNext . '</a>';
		}
		return $retval;
	}
}
