<?php

/**
 * Support Twitter Typeahead suggestions
 *
 * @link       https://twitter.github.io/typeahead.js/
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Mike Frysinger <vapier@gentoo.org>
 * @author     P.W.G. Brussee <brussee@live.nl>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
	die();
}

/**
 * Class action_plugin_data
 */
class action_plugin_csrlink extends DokuWiki_Action_Plugin {

	/**
	 * Registers a callback function for a given event
	 */
	function register(Doku_Event_Handler $controller) {
		$controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_handle_ajax');
	}

	/**
	 * @param Doku_Event $event
	 */
	function _handle_ajax(Doku_Event $event) {
		if ($event->data !== 'csrlink_wikisuggesties') {
			return;
		}

		$event->stopPropagation();
		$event->preventDefault();

		global $INPUT;

		$query = $INPUT->post->str('q');
		if (empty($query)) {
			$query = $INPUT->get->str('q');
		}
		if (empty($query)) {
			return;
		}

		$query = urldecode($query);
		$data = ft_pageLookup($query, false, useHeading('navigation'));

		if (!count($data)) {
			return;
		}

		$result = array();
		foreach ($data as $id => $title) {
			$label = '';
			if (useHeading('navigation')) {
				$name = $title;
			} else {
				$namespace = getNS($id);
				if ($namespace) {
					$name = noNS($id);
					$label = '<span class="lichtgrijs"> - ' . ucfirst($namespace) . '</span>';
				} else {
					$name = $id;
				}
			}
			$result[] = array(
				'url'	 => html_wikilink(':' . $id, $name),
				'value'	 => ucfirst($name) . $label
			);
		}

		header('Content-Type: application/json');
		echo $json->encode($result);
	}

}
