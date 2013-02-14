<?php

require_once DOKU_PLUGIN . 'confmanager/adminActions/ConfigManagerAdminAction.php';
require_once DOKU_PLUGIN . 'confmanager/adminActions/ConfigManagerAdminOverview.php';
require_once DOKU_PLUGIN . 'confmanager/adminActions/ConfigManagerAdminShowConfig.php';


class admin_plugin_confmanager extends DokuWiki_Admin_Plugin {

    /**
     * @var ConfigManagerAdminAction action to run
     */
    private $adminAction;

    public function getMenuSort() {
        return 101;
    }

    public function handle() {
        $this->determineAction();
        $this->adminAction->handle();
    }

    private function determineAction() {
        if (!isset($_REQUEST['configFile'])) {
            $this->adminAction = new ConfigManagerAdminOverview();
            return;
        }
        $this->adminAction = new ConfigManagerAdminShowConfig();
    }

    public function html() {
        echo '<div id="confmanager">';
        $this->adminAction->html();
        echo '</div>';
    }
}