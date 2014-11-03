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


/**
 * Syntax for refering documents from csrdelft.nl
 */
class syntax_plugin_csrlink_documentlink extends DokuWiki_Syntax_Plugin {
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
	 * @return int
	 */
	function getSort() {
        return 150;
    }


	/**
	 * @param string $mode
	 */
	function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\[\[document>.+?\]\]',$mode,'plugin_csrlink_documentlink');
    }


	/**
	 * Handler to prepare matched data for the rendering process
	 *
	 * @param   string       $match   The text matched by the patterns
	 * @param   int          $state   The lexer state for the match
	 * @param   int          $pos     The character position of the matched text
	 * @param   Doku_Handler $handler The Doku_Handler object
	 * @return  array Return an array with all data you want to use in render
	 */
	function handle($match, $state, $pos, &$handler){
        $match = trim(substr($match,11,-2));


        list($documentid,$title) = explode('|',$match,2);

        return compact('documentid','title');
    }


	/**
	 * Handles the actual output creation.
	 *
	 * The contents of the $data array depends on what the handler() function above
	 * created
	 *
	 * @param   $format   string        output format being rendered
	 * @param   $R Doku_Renderer the current renderer object
	 * @param   $data     array         data created by handler()
	 * @return  boolean                 rendered correctly?
	 */
	function render($format, Doku_Renderer $R, $data) {
        global $auth;
        /** @var string $title */
        /** @var string $documentid */
        extract($data);

        if($format != 'xhtml' || is_null($auth) || !$auth instanceof auth_plugin_authcsr){
            $R->cdata($title?$title:$documentid);
            return true;
        }

        require_once 'documenten/document.class.php';
        try{
            $document=new Document((int)$documentid);
            if($document->getID()===0) {
                throw new Exception('no document');
            }
        }catch(Exception $e){
            $R->doc .='<span class="csrlink invalid" title="[[document>]] Ongeldig document (id:'.hsc($documentid).')">'.hsc($title?$title:$documentid).'</span>';
            return true;
        }

        // get a nice title
        if($title=='fname'){
            $title = $document->getFileName();
        }elseif(!$title){
            $title = $document->getNaam();
        }

        //DokuWiki mimetype icons
        $documenturl = $document->getDownloadUrl();
        list($ext, /* $mime */, /* $dl */) = mimetype($documenturl,false);
        $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);

        //return html
        $R->doc .= '<a href="'.$documenturl.'" class="documentlink csrlink_plugin mediafile mf_'.$class.'">'.hsc($title).'</a> <span class="documentlink csrlink_plugin size">('.format_filesize((int)$document->getFileSize()).')</span>';

        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
