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

class syntax_plugin_csrlink_documentlink extends DokuWiki_Syntax_Plugin {
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
        $this->Lexer->addSpecialPattern('\[\[document>.+?\]\]',$mode,'plugin_csrlink_documentlink');
    }

    function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,11,-2));


        list($documentid,$title) = explode('|',$match,2);

        return compact('documentid','title');
    }

    function render($mode, &$R, $data) {
        global $auth;
        global $conf;
        extract($data);

        if($mode != 'xhtml' || is_null($auth)){
            $R->cdata($title?$title:$documentid);
            return true;
        }

        require_once 'documenten/document.class.php';
        try{
            $document=new Document((int)$documentid);
        }catch(Exception $e){
            $R->cdata( '[[document>]] Ongeldig document (id:'.$documentid.') ');
            return true;
        }

        // get a nice title
        if($title=='fname'){
            $title = $document->getBestandsnaam();
        }elseif(!$title){
            $title = $document->getNaam();
        }

        //DokuWiki mimetype icons
        $documenturl = $document->getDownloadurl();
        list($ext,$mime,$dl) = mimetype($documenturl,false);
        $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);

        //return html
        $R->doc .= '<a href="'.$documenturl.'" class="documentlink csrlink_plugin mediafile mf_'.$class.'">'.mb_htmlentities($title).'</a> <span class="documentlink csrlink_plugin size">('.format_filesize((int)$document->getSize()).')</span>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
