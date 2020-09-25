<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\Zijbalk;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class CsrTwigExtension extends AbstractExtension
{
	/**
	 * @var SessionInterface
	 */
	private $session;
	/**
	 * @var CsrfService
	 */
	private $csrfService;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(
		SessionInterface $session,
		CsrfService $csrfService,
		ProfielRepository $profielRepository
	)
	{
		$this->session = $session;
		$this->csrfService = $csrfService;
		$this->profielRepository = $profielRepository;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('dragobject_coords', [$this, 'dragobject_coords']),
			new TwigFunction('commitHash', 'commitHash'),
			new TwigFunction('commitLink', 'commitLink'),
			new TwigFunction('csrfMetaTag', [$this, 'csrfMetaTag'], ['is_safe' => ['html']]),
			new TwigFunction('csrfField', [$this, 'csrfField'], ['is_safe' => ['html']]),
			new TwigFunction('vereniging_leeftijd', 'vereniging_leeftijd'),
			new TwigFunction('get_profiel', [$this, 'get_profiel']),
			new TwigFunction('huidige_jaargang', [$this, 'huidige_jaargang']),
			new TwigFunction('gethostbyaddr', 'gethostbyaddr')
		];
	}

	public function huidige_jaargang()
	{
		return LichtingenRepository::getHuidigeJaargang();
	}

	public function get_profiel($uid)
	{
		return $this->profielRepository->find($uid);
	}

	public function csrfField($path = '', $method = 'post')
	{
		return (new CsrfField($this->csrfService->generateToken($path, $method)))->toString();
	}

	function csrfMetaTag()
	{
		$token = $this->csrfService->generateToken('', 'POST');
		return '<meta property="X-CSRF-ID" content="' . htmlentities($token->getId()) . '" /><meta property="X-CSRF-VALUE" content="' . htmlentities($token->getValue()) . '" />';
	}


	public function getFilters()
	{
		return [
			new TwigFilter('escape_ical', 'escape_ical'),
			new TwigFilter('file_base64', 'file_base64'),
			new TwigFilter('bbcode', [$this, 'bbcode'], ['is_safe' => ['html']]),
			new TwigFilter('bbcode_light', [$this, 'bbcode_light'], ['is_safe' => ['html']]),
			new TwigFilter('uniqid', function ($prefix) {
				return uniqid_safe($prefix);
			}),
			new TwigFilter('format_bedrag', 'format_bedrag'),
			new TwigFilter('truncate', 'truncate'),
			new TwigFilter('format_filesize', 'format_filesize'),
			new TwigFilter('shuffle', 'array_shuffle'),
		];
	}

	public function getTests()
	{
		/**
		 * @param Agendeerbaar $value
		 * @return bool
		 */
		/**
		 * @param Profiel $value
		 * @return bool
		 */
		return [
			new TwigTest('numeric', function ($value) {
				return is_numeric($value);
			}),
			new TwigTest('profiel', function ($value) {
				return $value instanceof Profiel;
			}),
			new TwigTest('corveetaak', function ($value) {
				return $value instanceof CorveeTaak;
			}),
			new TwigTest('maaltijd', function ($value) {
				return $value instanceof Maaltijd;
			}),
			new TwigTest('agendeerbaar', function ($value) {
				return $value instanceof Agendeerbaar;
			}),
			new TwigTest('abstractgroep', function ($value) {
				return $value instanceof AbstractGroep;
			}),
			new TwigTest('agendaitem', function ($value) {
				return $value instanceof AgendaItem;
			}),
		];
	}


	public function dragobject_coords($id, $top, $left)
	{
		if ($this->session->has("dragobject_$id")) {
			$dragObject = $this->session->get("dragobject_$id");
			$top = (int)$dragObject['top'];
			$left = (int)$dragObject['left'];
		}

		$top = max($top, 0);
		$left = max($left, 0);
		return ['top' => $top, 'left' => $left];
	}

	public function bbcode(string $string, string $mode = 'normal')
	{
		if ($mode === 'html') {
			return CsrBB::parseHtml($string);
		} else if ($mode == 'mail') {
			return CsrBB::parseMail($string);
		} else if ($mode == 'plain') {
			return CsrBB::parsePlain($string);
		} else {
			return CsrBB::parse($string);
		}
	}

	public function bbcode_light(string $string)
	{
		return CsrBB::parseLight($string);
	}

	public function file_base64($filename)
	{
		if (file_exists($filename)) {
			return base64_encode(file_get_contents($filename));
		}
		return '';
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

	/**
	 * Reken uit hoe oud de vereniging is.
	 *
	 * @return int
	 */
	public function vereniging_leeftijd()
	{
		$oprichting = date_create_immutable('1961-06-16');

		$leeftijd = date_create_immutable()->diff($oprichting);

		return $leeftijd->y;
	}
}

