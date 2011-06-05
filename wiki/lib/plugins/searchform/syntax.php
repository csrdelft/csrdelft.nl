<?php
/**
 * Plugin Search Form: Inserts a search form in any page
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Adolfo González Blázquez <code@infinicode.org>
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_searchform extends DokuWiki_Syntax_Plugin {
 
	function getInfo(){
		return array(
			'author' => 'Adolfo González Blázquez',
			'email'  => 'code@infinicode.org',
			'date'   => '2008-10-09',
			'name'   => 'Search Form Plugin',
			'desc'   => 'Inserts a search form in any page',
			'url'    => 'http://www.infinicode.org/code/dw/',
		);
    }
 
    function getType() { return 'substition'; }
    function getSort() { return 138; }
    
    function connectTo($mode) {
		$this->Lexer->addSpecialPattern('\{searchform\}',$mode,'plugin_searchform');
    }
    
    function handle($match, $state, $pos, &$handler) {  
    	return array($match, $state, $pos);
    }
    
    function render($mode, &$renderer, $data) {
 		
 		global $lang;
 		
		if ($mode == 'xhtml') {

			$renderer->doc .= '<div id="searchform_plugin">'."\n";
			$renderer->doc .= '<form action="'.wl().'" accept-charset="utf-8" class="search" id="dw__search"><div class="no">'."\n";
			$renderer->doc .= '<input type="hidden" name="do" value="search" />'."\n";
			$renderer->doc .= '<input type="text" ';
			if($ACT == 'search') $renderer->doc .= 'value="'.htmlspecialchars($_REQUEST['id']).'" ';
			if(!$autocomplete) $renderer->doc .= 'autocomplete="off" ';
			$renderer->doc .= 'id="qsearch__in" accesskey="f" name="id" class="edit" title="[ALT+F]" />'."\n";
			$renderer->doc .= '<input type="submit" value="'.$lang['btn_search'].'" class="button" title="'.$lang['btn_search'].'" />'."\n";
			if($ajax) $renderer->doc .= '<div id="qsearch__out" class="ajax_qsearch JSpopup"></div>'."\n";
			$renderer->doc .= '</div></form>'."\n";
			$renderer->doc .= '</div>'."\n";
			return true;
		}
		return false;
	}
}
?>
