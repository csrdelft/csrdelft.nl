<?php
/**
 * Action part of the DokuTesaer plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michael Hamann <michael@content-space.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Action class of the DokuTeaser plugin, handles section edit buttons
 */
class action_plugin_dokuteaser extends DokuWiki_Action_Plugin {
    /**
     * register the eventhandlers
     *
     * @param Doku_Event_Handler $controller The even controller
     */
    function register(&$controller){
        $controller->register_hook('HTML_SECEDIT_BUTTON', 'BEFORE', $this, 'handle_secedit_button');
    }

    /**
     * Handle section edit buttons, prevents section buttons inside the DokuTeaser plugin from being rendered
     *
     * @param Doku_Event $event The event object
     * @param array      $args Parameters for the event
     */
    public function handle_secedit_button($event, $args) {
        // counter of the number of currently opened wraps
        static $wraps = 0;
        $data = $event->data;

        if ($data['target'] == 'plugin_dokuteaser_start') {
            ++$wraps;
        } elseif ($data['target'] == 'plugin_dokuteaser_end') {
            --$wraps;
        } elseif ($wraps > 0 && $data['target'] == 'section') {
            $event->preventDefault();
            $event->stopPropagation();
            $event->result = '';
        }
    }

}