<?php
/**
 * DokuWiki Plugin csrlink (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Gerrit Uitslag
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Create link to C.S.R. profiel
 */
class syntax_plugin_csrlink_profiellink extends DokuWiki_Syntax_Plugin {

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType() {
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
     */
    function getPType() {
        return 'normal';
    }

    /**
     * Sort order when overlapping syntax
     * @return int
     */
    function getSort() {
        return 150;
    }

    /**
     * @param $mode
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[lid>.+?\]\]', $mode, 'plugin_csrlink_profiellink');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string $match   The text matched by the patterns
     * @param   int $state   The lexer state for the match
     * @param   int $pos     The character position of the matched text
     * @param   Doku_Handler $handler Reference to the Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, &$handler) {
        $match = trim(substr($match, 6, -2));

        list($uid, $title) = explode('|', $match, 2);

        return compact('uid', 'title');
    }

    /**
     * Handles the actual output creation.
     *
     * @param   $format   string        output format being rendered
     * @param   $renderer Doku_Renderer reference to the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($format, &$renderer, $data) {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        /** @var string $title */
        /** @var string $uid */
        extract($data);

        if($format != 'xhtml' || is_null($auth) || !$auth instanceof auth_plugin_authcsr) {
            $renderer->cdata($title ? $title : $uid);
            return true;
        }

        // fetch userinfo
        $uinfo = $auth->getUserData($uid);

        // nothing found? render as text
        if(!$uinfo) {
            $renderer->doc .= '<span class="csrlink invalid" title="[[lid>]] Geen geldig lidnummer (' . hsc($uid) . ')">' . hsc($title ? $title : $uid) . '</span>';
            return true;
        }

        if(!$title) {
            if($this->getConf('usefullname')) {
                $title = $uinfo['name'];
            } else {
                $title = $uid;
            }
        }
        if(!$title) $title = $uid;

        $renderer->doc .= '<a href="' . $this->getConf('profileurl') . $uid . '" class="profiellink_plugin">';
        $renderer->doc .= hsc($title);

        $renderer->doc .= '<span class="profiellink_popup" title="Bekijk Profiel">';
        $renderer->doc .= '<img src="' . $uinfo['pasfoto'] . '" class="medialeft" width="48" height="64" alt="" />';
        $renderer->doc .= '<b>' . hsc($uinfo['name']) . '</b><br />';
        if($uinfo['name'] != $uid) $renderer->doc .= '<i>' . hsc($uid) . '</i><br />';
        $renderer->doc .= '</span>';

        $renderer->doc .= '</a>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
