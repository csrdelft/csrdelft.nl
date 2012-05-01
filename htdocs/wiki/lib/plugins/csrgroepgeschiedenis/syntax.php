<?php
/**
 * DokuWiki Plugin csrgroepgeschiedenis (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_csrgroepgeschiedenis extends DokuWiki_Syntax_Plugin {
    public function getType() {  return 'substition'; }
    public function getPType() { return 'block'; }
    public function getSort() {  return 155; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<csrgroepgeschiedenis.+?</csrgroepgeschiedenis>',$mode,'plugin_csrgroepgeschiedenis');
    }

    public function handle($match, $state, $pos, &$handler){
        $match = substr($match, 21, -23);  // strip markup
        list($flags, $snaam) = explode('>', $match, 2);
        $flags = explode('&', substr($flags, 1));

        require_once 'groepen/groep.class.php';
        $geschiedenis = Groep::getGroepgeschiedenis($snaam, 70);

        $data = array($flags,$geschiedenis);
        return $data;
    }

    public function render($mode, &$renderer, $data) {
        if($mode != 'xhtml') return false;

        list($flags,$geschiedenis) = $data;

        // create a correctly nested list 
        $open = false;
        $lvl  = 1;
        $renderer->listu_open();
        foreach($geschiedenis as $groep){ 
            if($open) $renderer->listitem_close();
            $renderer->listitem_open($lvl);
            $open = true;
            
            $renderer->listcontent_open();
            $renderer->externallink($this->getConf('groepenurl').$groep['type'].'/'.$groep['id'], $groep['naam']);
            $renderer->listcontent_close();
        }
        $renderer->listitem_close();
        $renderer->listu_close();

        return true;
    }
}

// vim:ts=4:sw=4:et:
