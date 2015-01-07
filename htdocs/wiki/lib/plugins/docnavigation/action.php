<?php
/**
 * DokuWiki Plugin DocNavigation (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(DOKU_INC.'inc/parser/xhtml.php');

/**
 * Add documentation navigation elements around page
 */
class action_plugin_docnavigation extends DokuWiki_Action_Plugin {

    /**
     * Register the events
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('RENDERER_CONTENT_POSTPROCESS', 'AFTER', $this, '_addtopnavigation');
    }

    /**
     * Add navigation bar to top of content
     *
     * @param Doku_Event $event
     * @param            $param
     */
    public function _addtopnavigation(Doku_Event &$event, $param) {
        global $ACT;

        if($event->data[0] != 'xhtml' || !in_array($ACT, array('show', 'preview'))) return;

        $event->data[1] = $this->getNavbar($linktoToC = false)
                        . $event->data[1]
                        . $this->getNavbar($linktoToC = true);
    }

    /**
     * Return html of navigation elements
     *
     * @param bool $linktoToC add referer to ToC
     * @return string
     */
    private function getNavbar($linktoToC = true) {
        global $ID;
        global $ACT;
        $data = array();
        if($ACT == 'preview') {
            // the RENDERER_CONTENT_POSTPROCESS event is triggered just after rendering the instruction,
            // so syntax instance will exists
            /** @var syntax_plugin_docnavigation_pagenav $pagenav */
            $pagenav = plugin_load('syntax', 'docnavigation_pagenav');
            if($pagenav) {
                $data = $pagenav->data[$ID];
            }
        } else {
            $data = p_get_metadata($ID, 'docnavigation');
        }

        $out = '';
        if(!empty($data)) {
            /** @var Doku_Renderer_xhtml $Renderer */
            static $Renderer = null;
            if(is_null($Renderer)){
                $Renderer = p_get_renderer('xhtml');
            }

            if($linktoToC) {
                $out .= '<div class="clearer"></div>'.DOKU_LF;
            }

            $out .= '<div class="docnavbar'.($linktoToC ? ' showtoc' : '').'">'.DOKU_LF.DOKU_TAB.'<div class="leftnav">';
            if($data['previous'][0]) {
                $title = $this->getTitle($data['previous'], $Renderer);
                $out .= '← '.$Renderer->internallink($data['previous'][0], $title, null, true);
            }
            $out .= '&nbsp;</div>'.DOKU_LF;

            if($linktoToC) {
                $out .= DOKU_TAB.'<div class="centernav">';
                if($data['toc'][0]) {
                    $title = $this->getTitle($data['toc'], $Renderer);
                    $out .= $Renderer->internallink($data['toc'][0], $title, null, true);
                }
                $out .= '&nbsp;</div>'.DOKU_LF;
            }

            $out .= DOKU_TAB.'<div class="rightnav">&nbsp;';
            if($data['next'][0]) {
                $title = $this->getTitle($data['next'], $Renderer);
                $out .= $Renderer->internallink($data['next'][0], $title, null, true).' →';
            }
            $out .= '</div>'.DOKU_LF.'</div>'.DOKU_LF;
        }
        return $out;
    }

    /**
     * Build nice url title, if no title given use original link with original not cleaned id
     *
     * @param array $link
     * @param Doku_Renderer_xhtml $Renderer
     * @return string
     */
    protected function getTitle($link, $Renderer) {
        if($link[1] === null) {
            $defaulttitle = $Renderer->_simpleTitle($link[2]);
            return $Renderer->_getLinkTitle(null, $defaulttitle, $link[0]);
        }
        return $link[1];
    }
}

// vim:ts=4:sw=4:et:
