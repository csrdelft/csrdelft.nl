<?php
/**
 * Changes Plugin: List the most recent changes of the wiki
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Mykola Ostrovskyy <spambox03@mail.ru>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_changes
 */
class action_plugin_changes extends DokuWiki_Action_Plugin {

    /**
     * Register callbacks
     */
    public function register($controller) {
      $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'beforeParserCacheUse');
    }

    /**
     * Handle PARSER_CACHE_USE:BEFORE event
     */
    public function beforeParserCacheUse($event, $param) {
        global $ID;
        $cache = $event->data;
        if(isset($cache->mode) && ($cache->mode == 'xhtml')){
            $depends = p_get_metadata($ID, 'relation depends');
            if(!empty($depends) && isset($depends['rendering'])){
                $this->addDependencies($cache, array_keys($depends['rendering']));
            }
        }
    }

    /**
     * Add extra dependencies to the cache
     */
    protected function addDependencies($cache, $depends) {
        foreach($depends as $file){
            if(!in_array($file, $cache->depends['files']) && @file_exists($file)){
                $cache->depends['files'][] = $file;
            }
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
