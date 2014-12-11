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
	if (!LoginModel::mag('P_DOCS_READ') OR ! isset($_GET['q'])) {
		exit;
	} else {
		$zoekterm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
	}

	var_dump($zoekterm); //DEBUG

	$data = ft_pageLookup($zoekterm);

	var_dump($data); //DEBUG

	if (!count($data)) {
		return;
	}
	$data = array_keys($data);

	$limit = 5;
	if (isset($_GET['limit'])) {
		$limit = (int) $_GET['limit'];
	}
	$data = array_slice($data, 0, $limit);
	$data = array_map('trim', $data);
	$data = array_map('noNS', $data);
	$data = array_unique($data);
	sort($data);

	var_dump($data); //DEBUG

	$result = array();
	foreach ($data as $item) {
		$result[] = array(
			'url'	 => '/wiki/' . $item->getUrl(),
			'value'	 => $item->getNaam() . '<span class="lichtgrijs"> - ' . $item->getCategorie()->getNaam() . '</span>'
		);
	}

	header('Content-Type: application/json');
	echo json_encode($result);
	exit;
}
