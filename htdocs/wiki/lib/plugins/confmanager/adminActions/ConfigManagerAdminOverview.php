<?php

class ConfigManagerAdminOverview implements ConfigManagerAdminAction {

    /**
     * @var helper_plugin_confmanager
     */
    private $helper;

    public function __construct() {
        $this->helper = plugin_load('helper', 'confmanager');
    }

    public function handle() {}

    public function html() {
        $configFiles = $this->helper->getConfigFiles();
        $default = '';
        include DOKU_PLUGIN . 'confmanager/tpl/selectConfig.php';
    }

}