<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_data
 */
class action_plugin_cslink extends DokuWiki_Action_Plugin {

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



		$json = new JSON();
		header('Content-Type: application/json');
		echo $json->encode($result);
	}
}
