<?php

class action_plugin_confmanager_deleteicon extends DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_confmanager
     */
    var $helper;

    public function register(Doku_Event_Handler &$controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE',  $this, 'upload', array());
        $this->helper = plugin_load('helper', 'confmanager');
    }

    public function upload(Doku_Event &$event, $param) {
        if ($event->data !== 'confmanager_deleteIcon') {
            return;
        }

        $event->preventDefault();
        $event->stopPropagation();
        if (!auth_isadmin()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/plain');
            echo $this->getLang('upload_errNoAdmin');
            return;
        }

        $config = $this->getConfig();
        if ($config === false) {
            header('HTTP/1.1 405 Method Not Allowed');
            header('Content-Type: text/plain');
            echo $this->getLang('upload_errNoConfig');
            return;
        }

        if (!$config->deleteIcon()) {
            header('HTTP/1.1 400 Bad Request');
            return;
        }
        echo '1';
    }

    /**
     * @return bool|ConfigManagerUploadable
     */
    private function getConfig() {
        global $INPUT;
        $configId = $INPUT->str('configId', null, true);
        if ($configId === null) {
            return false;
        }

        $config = $this->helper->getConfigById($configId);
        if (!$config) {
            return false;
        }

        if (!($config instanceof ConfigManagerUploadable)) {
            return false;
        }
        return $config;
    }
}
