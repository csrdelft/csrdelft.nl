<?php
/**
 * DokuWiki Plugin DocNavigation (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Handles document navigation syntax
 */
class syntax_plugin_docnavigation_pagenav extends DokuWiki_Syntax_Plugin {

    /**
     * Stores data of navigation per page (for preview)
     *
     * @var array
     */
    public $data = array();

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     *
     * @return string
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Paragraph Type
     *
     * Defines how this syntax is handled regarding paragraphs. This is important
     * for correct XHTML nesting. Should return one of the following:
     *
     * 'normal' - The plugin can be used inside paragraphs
     * 'block'  - Open paragraphs need to be closed before plugin output
     * 'stack'  - Special case. Plugin wraps other paragraphs.
     *
     * @see Doku_Handler_Block
     *
     * @return string
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Sort for applying this mode
     *
     * @return int
     */
    public function getSort() {
        return 150;
    }

    /**
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<-[^\n]*\^[^\n]*\^[^\n]*->', $mode, 'plugin_docnavigation_pagenav');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * Usually you should only need the $match param.
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        global $conf, $ID;

        // links are: 0=previous, 1=toc, 2=next
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

        // store data for preview
        $this->data[$ID] = $data;

        // return instruction data for renderers
        return $data;
    }

    /**
     * Handles the actual output creation.
     *
     * @param string          $mode     output format being rendered
     * @param Doku_Renderer   $renderer the current renderer object
     * @param array           $data     data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            $renderer->meta['docnavigation'] = $data;

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
