<?php

class remote_plugin_confmanager extends DokuWiki_Remote_Plugin {

    /**
     * @var helper_plugin_confmanager
     */
    private $helper;

    function __construct() {
        $this->helper = $this->loadHelper('confmanager', null);
    }

    function _getMethods() {
        return array(
            'getConfigs' => array(
                'args' => array(),
                'return' => 'array'
            )
        );
    }

    function getConfigs() {
        $this->ensureAdmin();
        $this->helper->getConfigFiles();
        return $this->getApi()->toDate(time());
    }

    private function ensureAdmin() {
        if (!auth_isadmin()) {
            throw new RemoteAccessDeniedException();
        }
    }
}
