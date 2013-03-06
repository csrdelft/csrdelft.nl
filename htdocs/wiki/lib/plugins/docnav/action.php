<?php
/**
 * DokuWiki Plugin docnav (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_docnav extends DokuWiki_Action_Plugin {

    /**
     * Register the events
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler &$controller) {
        $controller->register_hook('RENDERER_CONTENT_POSTPROCESS', 'AFTER', $this, '_addtopnavigation');
        $controller->register_hook('TPL_ACT_RENDER', 'AFTER', $this, '_addbottomnavigation');
    }

    /**
     * Add navigation bar to top of content
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function _addtopnavigation(Doku_Event &$event, $param) {
        global $ACT;
        //if($ACT != 'show') return;
        if($event->data[0] != 'xhtml' || $ACT != 'show') return;

        $event->data[1] = $this->getNavbar($bottom = false).$event->data[1];
    }

    /**
     * Add navigationbar to bottom of content
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function _addbottomnavigation(Doku_Event &$event, $param) {
        if ($event->data != 'show') return;

        echo $this->getNavbar($bottom = true);
    }

    /**
     * Return html of navigation elements
     *
     * @param bool $bottom add referer to ToC
     * @return string
     */
    private function getNavbar($bottom = true) {
        global $ID;
        $data = p_get_metadata($ID, 'docnav');

        $out = '';
        if(!empty($data)) {
            $renderer = new Doku_Renderer_xhtml;

            if($bottom) $out .= '<div class="clearer"></div>';

            $out .= '<div class="docnavbar'.($bottom ? ' bottom' : '').'"><div class="leftnav">';
            if($data['previous']) $out .= '← '.$renderer->internallink($data['previous'], null, null, true);
            $out .= '&nbsp;</div>';

            if($bottom) {
                $out .= '<div class="centernav">';
                if($data['toc']) $out .= $renderer->internallink($data['toc'], null, null, true);
                $out .= '&nbsp;</div>';
            }

            $out .= '<div class="rightnav">&nbsp;';
            if($data['next']) $out .= $renderer->internallink($data['next'], null, null, true).' →';
            $out .= '</div></div>';
        }
        return $out;
    }
}

// vim:ts=4:sw=4:et:
