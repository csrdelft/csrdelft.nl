<?php
/**
 * DokuWiki Plugin docnav (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_docnav_pagenav extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 150;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<-[^\n]*\^[^\n]*\^[^\n]*->', $mode, 'plugin_docnav_pagenav');
    }

    public function handle($match, $state, $pos, &$handler) {
        global $conf, $ID;

        // links are: previous, toc, next
        $links = explode("^", substr($match, 2, -2), 3);
        foreach($links as &$link) {
            // Split title from URL
            $link = explode('|',$link,2);
            if ( !isset($link[1]) ) {
                $link[1] = NULL;
            } else if ( preg_match('/^\{\{[^\}]+\}\}$/',$link[1]) ) {
                // If the title is an image, convert it to an array containing the image details
                $link[1] = Doku_Handler_Parse_Media($link[1]);
            }
            $link[0] = trim($link[0]);
        }

        //look for an existing headpage when toc is empty
        if(!$links[1][0]) {
            $ns = getNS($ID);
            if(page_exists($ns.':'.$conf['start'])) {
                // start page inside namespace
                $links[1][0] = $ns.':'.$conf['start'];
            }elseif(page_exists($ns.':'.noNS($ns))) {
                // page named like the NS inside the NS
                $links[1][0] = $ns.':'.noNS($ns);
            }elseif(page_exists($ns)) {
                // page like namespace exists
                $links[1][0] = (!getNS($ns) ? ':':'').$ns;
            }
        }
        $data = array(
            'previous' => $links[0],
            'toc'      => $links[1],
            'next'     => $links[2]
        );
        return $data;
    }

    public function render($mode, &$renderer, $data) {

        if($mode == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            $renderer->meta['docnav'] = $data;

            foreach($data as $url) {
                if($url) {
                    $renderer->internallink($url[0], $url[1]);
                }
            }
            return true;
        }

        return false;
    }
}

// vim:ts=4:sw=4:et:
