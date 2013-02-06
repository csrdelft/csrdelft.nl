<?php
/**
 * DokuWiki Plugin templatepagename (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Martin <martin@sound4.biz>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_templatepagename_TemplatePageName extends DokuWiki_Action_Plugin {

    public function register(Doku_Event_Handler &$controller) {

       $controller->register_hook('COMMON_PAGETPL_LOAD', 'BEFORE', $this, 'handle_common_pagetpl_load');
   
    }

    public function handle_common_pagetpl_load(Doku_Event &$event, $param) {
	    global $conf;

	    // from here is it almost the same code as inc/common.php pageTemplate 
	    // function (core dokuwiki) but vars name are adjusted to be used 
	    // within the plugin.

	    $c_pagename = $this->getConf('current_pagename_tpl');
	    $i_pagename = $this->getConf('inherited_pagename_tpl');

	    $path = dirname(wikiFN($event->data['id']));

	    if(@file_exists($path.'/'.$c_pagename.'.txt')){
		    $event->data['tplfile'] = $path.'/'.$c_pagename.'.txt';
	    }else{
		    // search upper namespaces for templates
		    $len = strlen(rtrim($conf['datadir'],'/'));
		    while (strlen($path) >= $len){
			    if(@file_exists($path.'/'.$i_pagename.'.txt')){
				    $event->data['tplfile'] = $path.'/'.$i_pagename.'.txt';
				    break;
			    }
			    $path = substr($path, 0, strrpos($path, '/'));
		    }
	    }
    }

}

// vim:ts=4:sw=4:et:

