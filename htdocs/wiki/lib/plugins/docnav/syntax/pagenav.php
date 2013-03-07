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
        $this->Lexer->addSpecialPattern('<-[^\n]*\|[^\n]*\|[^\n]*->', $mode, 'plugin_docnav_pagenav');
    }

    public function handle($match, $state, $pos, &$handler) {
        global $conf, $ID;

        list($prev, $toc, $next) = explode("|", substr($match, 2, -2));

        if(!$toc) {
            $ns = getNS($ID);
            if(page_exists($ns.':'.$conf['start'])) {
                // start page inside namespace
                $toc = $ns.':'.$conf['start'];
            }elseif(page_exists($ns.':'.noNS($ns))) {
                // page named like the NS inside the NS
                $toc = $ns.':'.noNS($ns);
            }elseif(page_exists($ns)) {
                // page like namespace exists
                $toc = (!getNS($ns) ? ':':'').$ns;
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
