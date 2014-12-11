<?php

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
	global $INPUT;

	$query = cleanID($INPUT->post->str('q'));
	if (empty($query)) {
		$query = cleanID($INPUT->get->str('q'));
	}
	if (empty($query)) {
		return;
	}

	$data = ft_pageLookup($query);

	var_dump($data);

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

	$result = array();
	foreach (DocumentenCategorie::zoekDocumenten($zoekterm, $categorie, $limiet) as $doc) {
		$result[] = array(
			'url'	 => '/communicatie/documenten/bekijken/' . $doc->getID() . '/' . $doc->getFileName(),
			'value'	 => $doc->getNaam() . '<span class="lichtgrijs"> - ' . $doc->getCategorie()->getNaam() . '</span>'
		);
	}

	header('Content-Type: application/json');
	echo json_encode($result);
	exit;
}
