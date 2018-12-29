<?php

use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\renderer\TemplateView;

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
 * Genereer een unieke url voor een asset.
 *
 * @param string $asset
 * @return string
 */
function asset(string $asset) {
	$manifest = json_decode(file_get_contents(HTDOCS_PATH . 'dist/manifest.json'), true);

	if (isset($manifest[$asset])) {
		return CSR_ROOT . $manifest[$asset];
	} elseif (file_exists(HTDOCS_PATH . $asset)) {
		return CSR_ROOT . $asset . "?" . filemtime(HTDOCS_PATH . $asset);
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
	$txtPre = '';
	$txtFirst = '<<';
	$txtPrev = '<';
	$separator = ' ';
	$txtNext = '>';
	$txtLast = '>>';
	$txtPost = '';
	$txtSkip = '…';
	$cssClass = '';
	$linkCurrent = false;
	$showAlways = false;
	$showFirstLast = false;
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
	if (!$showAlways && $pagecount <= 1) {
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
	$retval = $txtPre;
	$cssClass = $cssClass ? 'class="' . $cssClass . '"' : '';
	if ($curpage > 1) {
		if ($showFirstLast) {
			$retval .= '<a href="' . $baseurl . '1' . $urlAppend . '" ' . $cssClass . '>' . $txtFirst . '</a>';
			$retval .= $separator;
		}
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
		if ($links[$i] != $curpage or $linkCurrent) {
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
		if ($showFirstLast) {
			$retval .= $separator;
			$retval .= '<a href="' . $baseurl . $pagecount . $urlAppend . '" ' . $cssClass . '>' . $txtLast . '</a>';
		}
	}
	$retval .= $txtPost;
	return $retval;
}

function bbcode(string $string, string $mode = 'normal') {
	if ($mode === 'html') {
		return CsrBB::parseHtml($string);
	} else {
		return CsrBB::parse($string);
	}
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
