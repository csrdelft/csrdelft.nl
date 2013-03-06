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
        $this->Lexer->addSpecialPattern('<.*?\|.*?\|.*?>', $mode, 'plugin_docnav_pagenav');
    }

    public function handle($match, $state, $pos, &$handler) {
        global $conf, $ID;

        //dbg($match);
        list($prev, $toc, $next) = explode("|", substr($match, 1, -1));
        if(!$toc) {
            $ns = getNS($ID);
            if(page_exists($ns.':'.$conf['start'])) {
                $toc = $ns.':'.$conf['start'];
            }
        }
        $data = array(
            'previous' => $prev,
            'next'     => $next,
            'toc'      => $toc
        );
        return $data;
    }

    public function render($mode, &$renderer, $data) {


        if($mode == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            $renderer->meta['docnav'] = $data;

            foreach($data as $url) {
                if($url) {
                    $renderer->internallink($url);
                }
            }

            return true;
        }
        return false;
    }
}

// vim:ts=4:sw=4:et:
