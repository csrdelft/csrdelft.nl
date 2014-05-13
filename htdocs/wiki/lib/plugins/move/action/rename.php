<?php
/**
 * Move Plugin Page Rename Functionality
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_move_rename
 */
class action_plugin_move_rename extends DokuWiki_Action_Plugin {

    /**
     * Register event handlers.
     *
     * @param Doku_Event_Handler $controller The plugin controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'handle_init');
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'handle_pagetools');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
    }

    /**
     * set JavaScript info if renaming of current page is possible
     */
    public function handle_init() {
        global $JSINFO;
        global $INFO;
        $JSINFO['move_renameokay'] = $this->renameOkay($INFO['id']);
    }

    /**
     * Adds a button to the default template
     *
     * @param Doku_Event $event
     */
    public function handle_pagetools(Doku_Event $event) {
        global $conf;
        if($event->data['view'] != 'main') return;

        switch($conf['template']) {
            case 'dokuwiki':
            case 'arago':

                $newitem = '<li class="plugin_move_page"><a href=""><span>' . $this->getLang('renamepage') . '</span></a></li>';
                $offset = count($event->data['items']) - 1;
                $event->data['items'] =
                    array_slice($event->data['items'], 0, $offset, true) +
                    array('plugin_move' => $newitem) +
                    array_slice($event->data['items'], $offset, null, true);
                break;
        }
    }

    /**
     * Rename a single page
     */
    public function handle_ajax(Doku_Event $event) {
        if($event->data != 'plugin_move_rename') return;
        $event->preventDefault();
        $event->stopPropagation();

        global $MSG;
        global $INPUT;

        $src = cleanID($INPUT->str('id'));
        $dst = cleanID($INPUT->str('newid'));

        /** @var helper_plugin_move_op $MoveOperator */
        $MoveOperator = plugin_load('helper', 'move_op');

        $JSON = new JSON();

        header('Content-Type: application/json');

        if($this->renameOkay($src) && $MoveOperator->movePage($src, $dst)) {
            // all went well, redirect
            echo $JSON->encode(array('redirect_url' => wl($dst, '', true, '&')));
        } else {
            if(isset($MSG[0])) {
                $error = $MSG[0]; // first error
            } else {
                $error = $this->getLang('cantrename');
            }
            echo $JSON->encode(array('error' => $error));
        }
    }

    /**
     * Determines if it would be okay to show a rename page button for the given page and current user
     *
     * @param $id
     * @return bool
     */
    public function renameOkay($id) {
        global $ACT;
        global $USERINFO;
        if(!($ACT == 'show' || empty($ACT))) return false;
        if(!page_exists($id)) return false;
        if(auth_quickaclcheck($id) < AUTH_EDIT) return false;
        if(checklock($id) !== false || @file_exists(wikiLockFN($id))) return false;
        if(!isset($_SERVER['REMOTE_USER'])) return false;
        if(!auth_isMember($this->getConf('allowrename'), $_SERVER['REMOTE_USER'], (array) $USERINFO['grps'])) return false;

        return true;
    }

    /**
     * Use this in your template to add a simple "move this page" link
     *
     * Alternatively give anything the class "plugin_move_page" - it will automatically be hidden and shown and
     * trigger the page move dialog.
     */
    public function tpl() {
        echo '<a href="" class="plugin_move_page">';
        echo $this->getLang('renamepage');
        echo '</a>';
    }

}