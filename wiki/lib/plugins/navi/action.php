<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_navi extends DokuWiki_Action_Plugin {

    /**
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('PARSER_CACHE_USE','BEFORE', $this, 'handle_cache_prepare');
    }

    /**
     * prepare the cache object for default _useCache action
     */
    function handle_cache_prepare(&$event, $param) {
        $cache =& $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) return;
        if ($cache->mode != 'i') return;

        // get meta data
        $depends = p_get_metadata($cache->page, 'relation naviplugin');
        if(!is_array($depends) || !count($depends)) return; // nothing to do
        $cache->depends['files'] = !empty($cache->depends['files']) ? array_merge($cache->depends['files'], $depends) : $depends;
    }
}
