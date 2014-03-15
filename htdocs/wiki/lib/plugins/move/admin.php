<?php
/**
 * Plugin : Move
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 * @author     Gary Owen,
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Admin component of the move plugin. Provides the user interface.
 */
class admin_plugin_move extends DokuWiki_Admin_Plugin {

    var $opts = array();
    private $ns_opts = false;
    /** @var helper_plugin_move $helper */
    private $helper = null;
    /** @var string $ns_move_state The state of the current namespace move (none, started, continued, error) */
    private $ns_move_state = 'none';

    /**
     * Get the sort number that defines the position in the admin menu.
     *
     * @return int The sort number
     */
    function getMenuSort() { return 1000; }

    /**
     * If this admin plugin is for admins only
     * @return bool false
     */
    function forAdminOnly() { return false; }

    /**
     * return some info
     */
    function getInfo(){
        $result = parent::getInfo();
        $result['desc'] = $this->getLang('desc');
        return $result;
    }

    /**
     * Only show the menu text for pages we can move or rename.
     */
    function getMenuText() {
        global $INFO;

        if( !$INFO['exists'] )
            return $this->getLang('menu').' ('.sprintf($this->getLang('notexist'), $INFO['id']).')';
        elseif( !$INFO['writable'] )
            return $this->getLang('menu').' ('.$this->getLang('notwrite').')';
        else
            return $this->getLang('menu');
    }



    /**
     * output appropriate html
     *
     * @author  Michael Hamann <michael@content-space.de>
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function html() {
        if (!$this->helper) return;
        ptln('<!-- Mmove Plugin start -->');
        ptln( $this->locale_xhtml('move') );
        ptln('<div class="plugin__move_forms">');

        switch ($this->ns_move_state) {
            case 'started':
                ptln('<p>');
                ptln(sprintf($this->getLang('ns_move_started'), hsc($this->ns_opts['ns']), hsc($this->ns_opts['newns']), $this->ns_opts['num_pages'], $this->ns_opts['num_media']));
                ptln('</p>');
                ptln($this->helper->getNSMoveButton('continue'));
                ptln($this->helper->getNSMoveButton('abort'));
                break;
            case 'error':
                ptln('<p>');
                ptln(sprintf($this->getLang('ns_move_error'), $this->ns_opts['ns'], $this->ns_opts['newns']));
                ptln('</p>');
                ptln($this->helper->getNSMoveButton('tryagain'));
                ptln($this->helper->getNSMoveButton('skip'));
                ptln($this->helper->getNSMoveButton('abort'));
                break;
            case 'continued':
                ptln('<p>');
                ptln(sprintf($this->getLang('ns_move_continued'), $this->ns_opts['ns'], $this->ns_opts['newns'], $this->ns_opts['remaining']));
                ptln('</p>');

                ptln($this->helper->getNSMoveButton('continue'));
                ptln($this->helper->getNSMoveButton('abort'));
                break;
            default:
                $this->printForm();
        }
        ptln('</div>');
        ptln('<!-- Move Plugin end -->');
    }

    /**
     * show the move and/or rename a page form
     *
     * @author  Michael Hamann <michael@content-space.de>
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function printForm() {
        global $ID;

        $ns = getNS($ID);

        $ns_select_data = $this->build_namespace_select_content($ns);

        $form = new Doku_Form(array('action' => wl($ID), 'method' => 'post', 'class' => 'move__form'));
        $form->addHidden('page', $this->getPluginName());
        $form->addHidden('id', $ID);
        $form->addHidden('move_type', 'page');
        $form->startFieldset($this->getLang('movepage'));
        $form->addElement(form_makeMenuField('ns_for_page', $ns_select_data, $this->opts['ns_for_page'], $this->getLang('targetns'), '', 'block'));
        $form->addElement(form_makeTextField('newns', $this->opts['newns'], $this->getLang('newtargetns'), '', 'block'));
        $form->addElement(form_makeTextField('newname', $this->opts['newname'], $this->getLang('newname'), '', 'block'));
        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('submit')));
        $form->endFieldset();
        $form->printForm();

        if ($this->ns_opts !== false) {
            ptln('<fieldset>');
            ptln('<legend>');
            ptln($this->getLang('movens'));
            ptln('</legend>');
            ptln('<p>');
            ptln(sprintf($this->getLang('ns_move_in_progress'), $this->ns_opts['num_pages'], $this->ns_opts['num_media'], ':'.hsc($this->ns_opts['ns']), ':'.hsc($this->ns_opts['newns'])));
            ptln('</p>');
            ptln($this->helper->getNSMoveButton('continue'));
            ptln($this->helper->getNSMoveButton('abort'));
            ptln('</fieldset>');
        } else {
            $form = new Doku_Form(array('action' => wl($ID), 'method' => 'post', 'class' => 'move__form'));
            $form->addHidden('page', $this->getPluginName());
            $form->addHidden('id', $ID);
            $form->addHidden('move_type', 'namespace');
            $form->startFieldset($this->getLang('movens'));
            $form->addElement(form_makeMenuField('targetns', $ns_select_data, $this->opts['targetns'], $this->getLang('targetns'), '', 'block'));
            $form->addElement(form_makeTextField('newnsname', $this->opts['newnsname'], $this->getLang('newnsname'), '', 'block'));
            $form->addElement(form_makeMenuField('contenttomove', array('pages' => $this->getLang('move_pages'), 'media' => $this->getLang('move_media'), 'both' => $this->getLang('move_media_and_pages')), $this->opts['contenttomove'], $this->getLang('content_to_move'), '', 'block'));
            $form->addElement(form_makeButton('submit', 'admin', $this->getLang('submit')));
            $form->endFieldset();
            $form->printForm();
        }
    }


    /**
     * create a list of namespaces for the html form
     *
     * @author  Michael Hamann <michael@content-space.de>
     * @author  Gary Owen <gary@isection.co.uk>
     * @author  Arno Puschmann (bin out of _form)
     */
    private function build_namespace_select_content($ns) {
        global $conf;

        $result = array();

        $namesp = array( 0 => array('id' => '') );     //Include root
        search($namesp, $conf['datadir'], 'search_namespaces', array());
        sort($namesp);
        foreach($namesp as $row) {
            if ( auth_quickaclcheck($row['id'].':*') >= AUTH_CREATE || $row['id'] == $ns ) {

                $result[($row['id'] ? $row['id'] : ':')] = ($row['id'] ? $row['id'].':' : ": ".$this->getLang('root')).
                                       ($row['id'] == $ns ? ' '.$this->getLang('current') : '');
            }
        }
        return $result;
    }


    /**
     * handle user request
     *
     * @author  Michael Hamann <michael@content-space.de>
     * @author  Gary Owen <gary@isection.co.uk>
     */
    function handle() {
        global $ID;
        global $ACT;
        global $INFO;

        // populate options with default values
        $this->opts['ns']          = getNS($ID);
        $this->opts['name']        = noNS($ID);
        $this->opts['ns_for_page'] = $INFO['namespace'];
        $this->opts['newns']       = '';
        $this->opts['newname']     = noNS($ID);
        $this->opts['targetns']    = getNS($ID);
        $this->opts['newnsname']   = '';
        $this->opts['move_type']   = 'page';
        $this->opts['contenttomove'] = 'pages';

        $this->helper = $this->loadHelper('move', true);
        if (!$this->helper) return;

        $this->ns_opts = $this->helper->get_namespace_move_opts();

        // Only continue when the form was submitted
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }

        if (isset($_POST['continue_namespace_move']) || isset($_POST['skip_continue_namespace_move'])) {
            if (isset($_POST['skip_continue_namespace_move'])) {
                $this->helper->skip_namespace_move_item();
            }
            $this->ns_opts['remaining'] = $this->helper->continue_namespace_move();
            $this->ns_move_state = ($this->ns_opts['remaining'] === false ? 'error': 'continued');
            if ($this->ns_opts['remaining'] === 0) {
                $ID = $this->helper->getNewID($INFO['id'], $this->opts['ns'], $this->opts['newns']);
                $ACT = 'show';
            }

            return;
        } elseif (isset($_POST['abort_namespace_move'])) {
            $this->helper->abort_namespace_move();
            $this->ns_opts = false;

            return;
        }

        // Store the form data in the options and clean the submitted data.
        if (isset($_POST['ns_for_page'])) $this->opts['ns_for_page'] = cleanID((string)$_POST['ns_for_page']);
        if (isset($_POST['newns'])) $this->opts['newns'] = cleanID((string)$_POST['newns']);
        if (isset($_POST['newname'])) $this->opts['newname'] = cleanID((string)$_POST['newname']);
        if (isset($_POST['targetns'])) $this->opts['targetns'] = cleanID((string)$_POST['targetns']);
        if (isset($_POST['newnsname'])) $this->opts['newnsname'] = cleanID((string)$_POST['newnsname']);
        if (isset($_POST['move_type'])) $this->opts['move_type'] = (string)$_POST['move_type'];
        if (isset($_POST['contenttomove']) && in_array($_POST['contenttomove'], array('pages', 'media', 'both'), true)) $this->opts['contenttomove'] = $_POST['contenttomove'];

        // check the input for completeness
        if( $this->opts['move_type'] == 'namespace' ) {

            if ($this->opts['targetns'] == '') {
                $this->opts['newns'] = $this->opts['newnsname'];
            } else {
                $this->opts['newns'] = $this->opts['targetns'].':'.$this->opts['newnsname'];
            }

            $started = $this->helper->start_namespace_move($this->opts);
            if ($started !== false) {
                $this->ns_opts = $this->helper->get_namespace_move_opts();
                $this->ns_move_state = 'started';
            }
        } else {
            // check that the pagename is valid
            if ($this->opts['newname'] == '' ) {
                msg($this->getLang('badname'), -1);
                return;
            }

            if ($this->opts['newns'] === '') {
                $this->opts['newns'] = $this->opts['ns_for_page'];
            }

            if ($this->helper->move_page($this->opts)) {
                // Set things up to display the new page.
                $ID = $this->opts['new_id'];
                $ACT = 'show'; // this triggers a redirect to the page
            }
        }
    }
}