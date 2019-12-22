<?php /** @noinspection PhpUnused wordt gebruikt in templates*/

use CsrDelft\common\CRLFView;
use CsrDelft\model\MenuModel;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\renderer\TemplateView;
use CsrDelft\view\View;

/**
 * Hulpmethodes die gebruikt worden in views.
 */

/**
 * Shorthand voor het aanmaken van een TemplateView
 *
 * @param string $template
 * @param array $variables
 * @return TemplateView
 */
function view(string $template, array $variables = []) {
	return new TemplateView($template, $variables);
}

/**
 * Shorthand voor het weergeven van een TemplateView
 *
 * @param string $template
 * @param array $variables
 * @throws Exception
 */
function display(string $template, array $variables = []) {
	(new TemplateView($template, $variables))->view();
}

/**
 * Zorgt dat line endings CRLF zijn voor ical en vcard.
 *
 * @param string input
 * @return string
 */
function crlf_endings(string $input) {
	return str_replace("\n", "\r\n", $input);
}

/**
 * Genereer een unieke url voor een asset.
 *
 * @param string $asset
 * @return string
 */
function asset(string $asset) {
	$manifest = json_decode(file_get_contents(HTDOCS_PATH . 'dist/manifest.json'), true);

	if (isset($manifest[$asset])) {
		return $manifest[$asset];
	} else {
		return '';
	}
}

/**
 * @param $date
 * @return false|string
 */
function rfc2822($date) {
	if (strlen($date) == strlen((int)$date)) {
		return date('r', $date);
	} else {
		return date('r', strtotime($date));
	}
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
function sliding_pager($params) {
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
	if ($curpage > 1) {
		if ($showPrevNext) {
			$retval .= '<a href="' . $baseurl . ($curpage - 1) . $urlAppend . '" ' . $cssClass . '>' . $txtPrev . '</a>';
			$retval .= $separator;
		}
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
		if (($links[$i] != $curpage)) {
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
	if ($curpage != $pagecount) {
		if ($showPrevNext) {
			$retval .= $separator;
			$retval .= '<a href="' . $baseurl . ($curpage + 1) . $urlAppend . '" ' . $cssClass . '>' . $txtNext . '</a>';
		}
	}
	return $retval;
}

function bbcode(string $string, string $mode = 'normal') {
	if ($mode === 'html') {
		return CsrBB::parseHtml($string);
	} else if ($mode == 'mail') {
		return CsrBB::parseMail($string);
	} else {
		return CsrBB::parse($string);
	}
}

function bbcode_light(string $string) {
	return CsrBB::parseLight($string);
}

/**
 * Formatteer een datum voor de zijbalk.
 *
 *  - Als dezelfe dag:     13:13
 *  - Als dezelfde maand:  ma 06
 *  - Anders:              06-12
 *
 * @version 1.0
 * @param string|integer
 * @return string
 */
function zijbalk_date_format($datetime) {
	if (!is_int($datetime)) {
		$datetime = strtotime($datetime);
	}

	if (date('d-m', $datetime) === date('d-m')) {
		return strftime('%H:%M', $datetime);
	} elseif (strftime('%U', $datetime) === strftime('%U')) {
		return strftime('%a&nbsp;%d', $datetime);
	} else {
		return strftime('%d-%m', $datetime);
	}
}

function link_for($title, $href, $class, $activeClass) {
	if ($_SERVER['REQUEST_URI'] == $href) {
		$class .= ' ' . $activeClass;
	}

	return '<a href="' . $href . '" class="' . $class . '">' . $title . '</a>';
}

/**
 * @param int $bedrag Bedrag in centen
 * @return string
 */
function format_bedrag($bedrag) {
	return sprintf('€%.2f', $bedrag / 100);
}

/**
 * @param string $string input string
 * @param integer $length length of truncated text
 * @param string $etc end string
 * @param boolean $break_words truncate at word boundary
 * @param boolean $middle truncate in the middle of text
 *
 * @return string truncated string
 */
function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
	if ($length === 0) {
		return '';
	}
	if (mb_strlen($string, 'UTF-8') > $length) {
		$length -= min($length, mb_strlen($etc, 'UTF-8'));
		if (!$break_words && !$middle) {
			$string = preg_replace(
				'/\s+?(\S+)?$/u',
				'',
				mb_substr($string, 0, $length + 1, 'UTF-8')
			);
		}
		if (!$middle) {
			return mb_substr($string, 0, $length, 'UTF-8') . $etc;
		}
		return mb_substr($string, 0, $length / 2, 'UTF-8') . $etc .
			mb_substr($string, -$length / 2, $length, 'UTF-8');
	}
	return $string;
}

/**
 * Finds the position of the first space before a given offset.
 *
 * @param string $string
 * @param int $offset
 * @return bool|int
 */
function first_space_before(string $string, int $offset = null) {
	return mb_strrpos(substr($string, 0, $offset), ' ') + 1;
}

/**
 * Finds the position of the first space after a given offset.
 *
 * @param string $string
 * @param int $offset
 * @return bool|int
 */
function first_space_after(string $string, int $offset = null) {
	return mb_strpos($string, ' ', $offset);
}

/**
 * Split a string on keyword with a given space (in characters) around the keyword. Splits on spaces.
 *
 * @param string $string The base string
 * @param string $keyword The keyword to split on
 * @param int $space_around Amount of characters after which a split should occur
 * @param int $threshold Least amount of characters that should be hidden for a split to occur
 * @param string $ellipsis Character(s) to use as ellipsis character. default: …
 * @return string
 */
function split_on_keyword(string $string, string $keyword, int $space_around = 100, int $threshold = 10, string $ellipsis = '…') {
	$prevPos = $lastPos = 0;
	$firstPos = mb_stripos($string, $keyword);

	if ($firstPos === false && mb_strlen($string)) {
		return mb_substr($string, 0, $space_around * 2) . $ellipsis;
	}

	if ($firstPos > $space_around) {
		$split = first_space_before($string, $firstPos - $space_around);

		if ($split > $threshold) {
			$string = $ellipsis . mb_substr($string, $split);
			$prevPos = mb_strlen($ellipsis) + $split + mb_strlen($keyword);
		}
	}

	while ($prevPos < mb_strlen($string) && ($lastPos = mb_stripos($string, $keyword, $prevPos)) !== false) {
		// Split and insert ellipsis if the space between keywords is large enough.
		if ($lastPos - $prevPos > 2 * $space_around) {
			$split_l = first_space_after($string, $prevPos + $space_around);
			$split_r = first_space_before($string, $lastPos - $space_around);

			// Only do the split if enough characters are hidden by splitting
			if ($split_r - $split_l > $threshold) {
				$string = mb_substr($string, 0, $split_l) . $ellipsis . mb_substr($string, $split_r);
				$prevPos = $split_l + 2 * ($split_r - $split_l) + mb_strlen($ellipsis) + mb_strlen($keyword);

				continue;
			}
		}

		$prevPos = $lastPos + mb_strlen($keyword);
	}

	if ($prevPos + $space_around < mb_strlen($string)) {
		$string = mb_substr($string, 0, first_space_after($string, $prevPos + $space_around)) . $ellipsis;
	}

	return $string;
}

function highlight_zoekterm($bericht, $zoekterm, $before = null, $after = null) {
	$before = $before ?: '<span style="background-color: rgba(255,255,0,0.4);">';
	$after = $after ?: '</span>';
	return preg_replace('/' . preg_quote($zoekterm, '/') . '/i', $before . '$0' . $after, $bericht);
}

function csr_breadcrumbs($breadcrumbs) {
	return MenuModel::instance()->renderBreadcrumbs($breadcrumbs);
}

/**
 * Ical escape modifier plugin
 * Type:     modifier<br>
 * Name:     escape_ical<br>
 * Purpose:  escape string for ical output
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @param string $string
 * @param int $prefix_length
 *
 * @return string
 */
function escape_ical($string, $prefix_length) {
	$string = str_replace('\\', '\\\\', $string);
	$string = str_replace("\r", '', $string);
	$string = str_replace("\n", '\n', $string);
	$string = str_replace(';', '\;', $string);
	$string = str_replace(',', '\,', $string);

	$length = 60 - (int)$prefix_length;
	$wrap = mb_substr($string, 0, $length);
	$rest = mb_substr($string, $length);
	if (!empty($rest)) {
		$wrap .= "\r\n " . wordwrap($rest, 59, "\r\n ", true);
	}
	return $wrap;
}

function commitHash($full = false) {
	if ($full) {
		return trim(`git rev-parse HEAD`);
	} else {
		return trim(`git rev-parse --short HEAD`);
	}
}

function commitLink() {
	return 'https://github.com/csrdelft/productie/commit/' . commitHash(true);
}
