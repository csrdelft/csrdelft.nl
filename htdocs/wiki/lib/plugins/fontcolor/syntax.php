<?php
/**
 * fontcolor Plugin: Allows user-defined font colors
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     modified by ThorstenStratmann <thorsten.stratmann@web.de>
 * @link       http://www.dokuwiki.org/plugin:fontcolor
 * @version    3.1
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_fontcolor extends DokuWiki_Syntax_Plugin {
 
    function getInfo(){  // return some info
        return array(
            'author' => 'ThorstenStratmann',
            'email'  => 'thorsten.stratmann@web.de',
            'date'   => '2009-02-04',
            'name'   => 'fontcolor Plugin',
            'desc'   => 'color text with a specific color
                         Syntax: <fc color>Your Text </fc>',
            'url'    => 'http://www.dokuwiki.org/plugin:fontcolor',
        );
    }
 
     // What kind of syntax are we?
    function getType(){ return 'formatting'; }
 
    // What kind of syntax do we allow (optional)
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }
 
   // What about paragraphs? (optional)
   function getPType(){ return 'normal'; }
 
    // Where to sort in?
    function getSort(){ return 90; }
 
 
    // Connect pattern to lexer
    function connectTo($mode) {
      $this->Lexer->addEntryPattern('(?i)<fc(?: .+?)?>(?=.+</fc>)',$mode,'plugin_fontcolor');
    }
    function postConnect() {
      $this->Lexer->addExitPattern('(?i)</fc>','plugin_fontcolor');
    }
 
 
    // Handle the match
    function handle($match, $state, $pos, &$handler){
        switch ($state) {
          case DOKU_LEXER_ENTER :
            preg_match("/(?i)<fc (.+?)>/", $match, $color); // get the color
            if ( $this->_isValid($color[1]) ) return array($state, $color[1]);
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            return array($state, $match);
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            break;
        }
        return array($state, "#ff0");
    }
 
    // Create output
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
          list($state, $color) = $data;
          switch ($state) {
            case DOKU_LEXER_ENTER :
              $renderer->doc .= "<span style=\"color: $color\">";
              break;
            case DOKU_LEXER_MATCHED :
              break;
            case DOKU_LEXER_UNMATCHED :
              $renderer->doc .= $renderer->_xmlEntities($color);
              break;
            case DOKU_LEXER_EXIT :
              $renderer->doc .= "</span>";
              break;
            case DOKU_LEXER_SPECIAL :
              break;
          }
          return true;
        }
        return false;
    }
 
    // validate color value $c
    // this is cut price validation - only to ensure the basic format is
    // correct and there is nothing harmful
    // three basic formats  "colorname", "#fff[fff]", "rgb(255[%],255[%],255[%])"
    function _isValid($c) {
 
        $c = trim($c);
 
        $pattern = "/
            (^[a-zA-Z]+$)|                                #colorname - not verified
            (^\#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$)|        #colorvalue
            (^rgb\(([0-9]{1,3}%?,){2}[0-9]{1,3}%?\)$)     #rgb triplet
            /x";
 
        return (preg_match($pattern, $c));
 
    }
}
 
//Setup VIM: ex: et ts=4 sw=4 enc=utf-8 :