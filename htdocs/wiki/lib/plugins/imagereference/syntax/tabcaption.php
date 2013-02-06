<?php
/**
 * Plugin imagereference
 *
 * Syntax: <tabref linkname> - creates a table link to a table
 *         <tabcaption linkname <orientation> | Image caption> Image/Table</tabcaption>
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
class syntax_plugin_imagereference_tabcaption extends syntax_plugin_imagereference_imgcaption {

    /**
     * @return string Syntax type
     */
    function getType() {
        return 'formatting';
    }
    /**
     * @return string Paragraph type
     */
    function getPType() {
        return 'block';
    }
    /**
     * @return int Sort order
     */
    function getSort() {
        return 196;
    }

    /**
     * Specify modes allowed in the imgcaption/tabcaption
     * Using getAllowedTypes() includes too much modes.
     *
     * @param string $mode Parser mode
     * @return bool true if $mode is accepted
     */
    function accepts($mode) {
        if($mode == 'table') return true;

        return parent::accepts($mode);
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<tabcaption.*?>(?=.*?</tabcaption>)', $mode, 'plugin_imagereference_tabcaption');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('</tabcaption>', 'plugin_imagereference_tabcaption');
    }
}

