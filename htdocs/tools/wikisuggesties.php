<?php

require_once 'configuratie.include.php';

/**
 * DokuWiki Twitter Typeahead creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       https://twitter.github.io/typeahead.js/
 * @author     Mike Frysinger <vapier@gentoo.org>
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     P.W.G. Brussee <brussee@live.nl>
 */
if (!defined('DOKU_INC'))
	define('DOKU_INC', HTDOCS_PATH . 'wiki/');
if (!defined('NOSESSION'))
	define('NOSESSION', true); // we do not use a session or authentication here (better caching)
if (!defined('NL'))
	define('NL', "\n");
require_once(DOKU_INC . 'inc/init.php');

require_once(DOKU_INC . 'lib/exe/ajax.php');

/**
 * Support Twitter Typeahead suggestions
 *
 * @link   https://twitter.github.io/typeahead.js/
 * @author Mike Frysinger <vapier@gentoo.org>
 * @author P.W.G. Brussee <brussee@live.nl>
 */
function ajax_ttypeahead() {
	if (!LoginModel::mag('P_LEDEN_READ') OR ! isset($_GET['q'])) {
		exit;
	} else {
		$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
	}

	$limit = 5;
	if (isset($_GET['limit'])) {
		$limit = (int) $_GET['limit'];
	}

	var_dump($query); //DEBUG

	$data = ft_pageLookup($query, true, useHeading('navigation'));

	var_dump($data); //DEBUG

	$result = array();
	$counter = 0;
	foreach ($data as $id => $title) {
		$label = '';
		if (useHeading('navigation')) {
			$name = $title;
		} else {
			$namespace = getNS($id);
			if ($namespace) {
				$name = noNS($id);
				$label = '<span class="lichtgrijs"> - ' . $namespace . '</span>';
			} else {
				$name = $id;
			}
		}
		$result[] = array(
			'url'	 => html_wikilink(':' . $id, $name),
			'value'	 => $name . $label
		);
		if ($counter++ > $limit) {
			break;
		}
	}

	header('Content-Type: application/json');
	echo json_encode($result);
}
