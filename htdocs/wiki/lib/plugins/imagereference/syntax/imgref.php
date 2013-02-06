<?php
    /**
     * Plugin imagereference
     *
     * Syntax: <imgref linkname> - creates a figure link to an image
     *         <tabref linkname> - creates a table link to a table
     *
     * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
     * @author     Martin Heinemann <info@martinheinemann.net>
     * @author     Gerrit Uitslag <klapinklapin@gmail.com>
     */

    if(!defined('DOKU_INC')) die();

    if(!defined('DOKU_LF')) define('DOKU_LF', "\n");
    if(!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
    if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

    require_once DOKU_PLUGIN.'syntax.php';
    /**
     * All DokuWiki plugins to extend the parser/rendering mechanism
     * need to inherit from this class
     */
class syntax_plugin_imagereference_imgref extends DokuWiki_Syntax_Plugin {

    /**
     * @return string Syntax type
     */
    function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    function getPType() {
        return 'normal';
    }
    /**
     * @return int Sort order
     */
    function getSort() {
        return 197;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<imgref.*?>', $mode, 'plugin_imagereference_imgref');
        $this->Lexer->addSpecialPattern('<tabref.*?>', $mode, 'plugin_imagereference_imgref');

    }
    /**
     * Handle matches of the imgref syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, &$handler) {
        $reftype = substr($match, 1, 3);
        $ref = trim(substr($match, 8, -1));
        if($ref) {
            return array(
                'caprefname' => $ref,
                'type'    => $reftype
            );
        }
        return false;
    }
    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml and metadata)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler function
     * @return bool If rendering was successful.
     */
    function render($mode, &$renderer, $data) {
        global $ID;
        if($data === false) return false;

        switch($mode) {
            case 'xhtml' :
                /** @var Doku_Renderer_xhtml $renderer */

                //determine referencenumber
                $caprefs   = p_get_metadata($ID, 'captionreferences '.$data['type']);
                $refNumber = array_search($data['caprefname'], $caprefs);

                if(!$refNumber) {
                    $refNumber = "##";
                }

                $renderer->doc .= '<a href="#'.$data['type'].'_'.cleanID($data['caprefname']).'">'.$this->getLang($data['type'].'full').' '.$refNumber.'</a>';
                return true;

            case 'latex' :
                $renderer->doc .= "\\ref{".$data['caprefname']."}";
                return true;
        }
        return false;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :