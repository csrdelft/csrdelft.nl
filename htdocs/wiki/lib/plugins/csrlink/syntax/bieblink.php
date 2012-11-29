<?php
/**
 * DokuWiki Plugin csrlink (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Gerrit Uitslag
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_csrlink_bieblink extends DokuWiki_Syntax_Plugin {
    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'normal';
    }

    function getSort() {
        return 150;
    }


    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[boek>.+?\]\]',$mode,'plugin_csrlink_bieblink');
    }

    function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,7,-2));


        list($boekid,$title) = explode('|',$match,2);

        return compact('boekid','title');
    }

    function render($mode, &$R, $data) {
        global $auth;
        global $conf;
        extract($data);

        if($mode != 'xhtml' || is_null($auth) || !$auth instanceof auth_csr){
            $R->cdata($title?$title:$boekid);
            return true;
        }

        require_once 'bibliotheek/boek.class.php';
        try{
            $boek =    new Boek($boekid);
        }catch(Exception $e){
            // nothing found? render as text
            $R->doc .='<span class="csrlink invalid" title="[[boek>]] Geen geldig boek-id ('.hsc($boekid).')">'.hsc($title?$title:$boekid).'</span>';
            return true;
        }

        // get a nice title
        if(!$title){
            $title = $boek->getTitel();
        }

        //return html
        $R->doc .= '<a class="bieblink groeplink_plugin" href="'.$boek->getUrl().'" title="Boek: '.hsc($boek->getTitel()).'">';
	    $R->doc .= '<span title="'.$boek->getStatus().' boek" class="boekindicator '.$boek->getStatus().'">•</span><span class="titel">'.$title.'</span> <span class="auteur">('.hsc($boek->getAuteur()).')</span>';
        $R->doc .= '</a>';
        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
