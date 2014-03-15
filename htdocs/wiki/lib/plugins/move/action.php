<?php
/**
 * Plugin : Move
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Action part of the move plugin
 */
class action_plugin_move extends DokuWiki_Action_Plugin {
    /**
     * Register event handlers.
     *
     * @param Doku_Event_Handler $controller The plugin controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('IO_WIKIPAGE_READ', 'AFTER', $this, 'handle_read', array());
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_cache', array());
        $controller->register_hook('INDEXER_VERSION_GET', 'BEFORE', $this, 'handle_index_version');
        $controller->register_hook('INDEXER_PAGE_ADD', 'BEFORE', $this, 'index_media_use');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call');
   }

    /**
     * Rewrite pages when they are read and they need to be updated.
     *
     * @param Doku_Event $event The event object
     * @param mixed      $param Optional parameters (not used)
     */
    function handle_read(Doku_Event $event, $param) {
        global $ACT, $conf;
        static $stack = array();
        // handle only reads of the current revision
        if ($event->data[3]) return;

        $id = $event->data[2];
        if ($event->data[1]) $id = $event->data[1].':'.$id;

        if (!$id) {
            // try to reconstruct the id from the filename
            $path = $event->data[0][0];
            if (strpos($path, $conf['datadir']) === 0) {
                $path = substr($path, strlen($conf['datadir'])+1);
                $id = pathID($path);
            }
        }

        if (isset($stack[$id])) return;

        // Don't change the page when the user is currently changing the page content or the page is locked
        $forbidden_actions = array('save', 'preview', 'recover', 'revert');
        if ((isset($ACT) && (
                    in_array($ACT, $forbidden_actions) || (is_array($ACT) && in_array(key($ACT), $forbidden_actions)
                    )))
            // checklock checks if the page lock hasn't expired and the page hasn't been locked by another user
            // the file exists check checks if the page is reported unlocked if a lock exists which means that
            // the page is locked by the current user
            || checklock($id) !== false || @file_exists(wikiLockFN($id))) return;

        /** @var helper_plugin_move $helper */
        $helper = $this->loadHelper('move', true);
        if(!is_null($helper)) {
            $stack[$id]    = true;
            $event->result = $helper->execute_rewrites($id, $event->result);
            unset($stack[$id]);
        }
    }

    /**
     * Handle the cache events, it looks if a page needs to be rewritten so it can expire the cache of the page
     *
     * @param Doku_Event $event The even object
     * @param mixed      $param Optional parameters (not used)
     */
    function handle_cache(Doku_Event $event, $param) {
        global $conf;
        /** @var $cache cache_parser */
        $cache = $event->data;
        $id = $cache->page;
        if (!$id) {
            // try to reconstruct the id from the filename
            $path = $cache->file;
            if (strpos($path, $conf['datadir']) === 0) {
                $path = substr($path, strlen($conf['datadir'])+1);
                $id = pathID($path);
            }
        }
        if ($id) {
            $meta = p_get_metadata($id, 'plugin_move', METADATA_DONT_RENDER);
            if ($meta && (isset($meta['moves']) || isset($meta['media_moves']))) {
                $file = wikiFN($id, '', false);
                if (is_writable($file))
                    $cache->depends['purge'] = true;
                else // FIXME: print error here or fail silently?
                    msg('Error: Page '.hsc($id).' needs to be rewritten because of page renames but is not writable.', -1);
            }
        }
    }

    /**
     * Add the move version to theindex version
     *
     * @param Doku_Event $event The event object
     * @param array $param Optional parameters (unused)
     */
    public function handle_index_version(Doku_Event $event, $param) {
        // From indexer version 6 on the media references are indexed by DokuWiki itself
        if ($event->data['dokuwiki'] < 6)
            $event->data['plugin_move'] = 0.2;
    }

    /**
     * Index media usage data
     *
     * @param Doku_Event $event The event object
     * @param array $param  Optional parameters (unused)
     */
    public function index_media_use(Doku_Event $event, $param) {
        // From indexer version 6 on the media references are indexed by DokuWiki itself
        if (INDEXER_VERSION >= 6) return;
        $id = $event->data['page'];
        $media_references = array();
        $instructions = p_cached_instructions(wikiFn($id), false, $id);
        if (is_array($instructions)) {
            $this->get_media_references_from_instructions($instructions, $media_references, $id);
        }
        $media_references = array_unique($media_references);
        $event->data['metadata']['relation_media'] = $media_references;
    }

    /**
     * Helper function for getting all media references from an instruction array
     *
     * @param array $instructions The instructions to scan
     * @param array $media_references The array of media references
     * @param string $id The reference id for resolving media ids
     */
    private function get_media_references_from_instructions($instructions, &$media_references, $id) {
        foreach ($instructions as $ins) {
            if ($ins[0] === 'internalmedia') {
                $src = $ins[1][0];
                list($src,$hash) = explode('#',$src,2);
                resolve_mediaid(getNS($id),$src, $exists);
                $media_references[] = $src;
            } elseif (in_array($ins[0], array('interwikilink', 'windowssharelink', 'externallink', 'emaillink', 'locallink', 'internallink'))) {
                $img = $ins[1][1];
                if (is_array($img) && $img['type'] === 'internalmedia') {
                    list($src,$hash) = explode('#',$img['src'],2);
                    resolve_mediaid(getNS($id), $src, $exists);
                    $media_references[] = $src;
                }
            } elseif ($ins[0] === 'nest') {
                // nested instructions
                $this->get_media_references_from_instructions($ins[1][0], $media_references, $id);
            } elseif ($ins[0] === 'plugin' && $ins[1][0] === 'variants_variants') {
                // the variants plugin has two branches with nested instructions, both need to be rewritten
                $this->get_media_references_from_instructions($ins[1][1][1], $media_references, $id);
                $this->get_media_references_from_instructions($ins[1][1][2], $media_references, $id);
            }
        }
    }

    /**
     * Handle the plugin_move_ns_continue ajax call
     *
     * @param Doku_Event $event The event that is handled
     * @param array $params Optional parameters (unused)
     */
    public function handle_ajax_call(Doku_Event $event, $params) {
        if ($event->data == 'plugin_move_ns_continue') {
            $event->preventDefault();
            $event->stopPropagation();

            /** @var helper_plugin_move $helper */
            $helper = $this->loadHelper('move', false);
            $opts = $helper->get_namespace_move_opts();
            $id = cleanID((string)$_POST['id']);
            $skip = (string)$_POST['skip'];
            if ($opts !== false) {
                if ($skip == 'true') {
                    $helper->skip_namespace_move_item();
                }
                $remaining = $helper->continue_namespace_move();
                $newid = $helper->getNewID($id, $opts['ns'], $opts['newns']);

                $result = array();
                $result['remaining'] = $remaining;
                $result['pages'] = $opts['num_pages'];
                $result['media'] = $opts['num_media'];
                $result['redirect_url'] = wl($newid, '', true);
                ob_start();
                html_msgarea();
                if ($remaining === false) {
                    ptln('<p>'.sprintf($this->getLang('ns_move_error'), $opts['ns'], $opts['newns']).'</p>');
                    echo $helper->getNSMoveButton('tryagain', $id);
                    echo $helper->getNSMoveButton('skip', $id);
                    echo $helper->getNSMoveButton('abort', $id);
                } else {
                    ptln('<p>'.sprintf($this->getLang('ns_move_continued'), $opts['ns'], $opts['newns'], $remaining).'</p>');
                }
                $result['html'] = ob_get_clean();
            } else {
                $result = array();
                $result['remaining'] = 0;
                $result['pages'] = 0;
                $result['media'] = 0;
                $result['redirect_url'] = wl('', '', true);
                $result['html'] = '';
            }
            $json = new JSON();
            echo $json->encode($result);
        }
    }
}