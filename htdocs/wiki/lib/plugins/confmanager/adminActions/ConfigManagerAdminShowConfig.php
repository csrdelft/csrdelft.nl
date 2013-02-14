<?php

class ConfigManagerAdminShowConfig implements ConfigManagerAdminAction {

    /**
     * @var helper_plugin_confmanager
     */
    private $helper;
    private $configId;

    /**
     * @var ConfigManagerConfigType
     */
    private $config;

    public function __construct() {
        $this->helper = plugin_load('helper', 'confmanager');
    }

    public function handle() {
        global $INPUT;
        global $ID;
        global $JSINFO;

        $this->configId = $INPUT->str('configFile');
        $this->config = $this->helper->getConfigById($this->configId);
        if ($this->config === false) {
            $params = array(
                'do' => 'admin',
                'page' => 'confmanager'
            );
            send_redirect(wl($ID, $params, false, '&'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!checkSecurityToken()) {
                msg($this->helper->getLang('invalid request csrf'), -1);
            }

            $this->config->save();
            @touch(DOKU_INC . 'conf/local.php');

            $params = array(
                'do' => 'admin',
                'page' => 'confmanager',
                'configFile' => $this->configId
            );
            send_redirect(wl($ID, $params, false, '&'));
        }
        $JSINFO['configId'] = $this->configId;
    }

    public function html() {
        $this->header();
        $this->displayDescription();
        $this->displayConfig();
    }

    private function header() {
        $configFiles = $this->helper->getConfigFiles();
        $default = $this->configId;
        include DOKU_PLUGIN . 'confmanager/tpl/selectConfig.php';
    }

    public function displayConfig() {
        $this->formStart();
        formSecurityToken();
        $this->config->display();
        $this->formEnd();
    }

    private function formStart() {
        $id = $this->configId;
        include DOKU_PLUGIN . 'confmanager/tpl/formStart.php';
    }

    private function formEnd() {
        include DOKU_PLUGIN . 'confmanager/tpl/formEnd.php';
    }

    private function displayDescription() {
        $description = $this->config->getDescription();
        if (empty($description)) {
            return;
        }

        
        $configHeadLine = $this->config->getName();
        include DOKU_PLUGIN . 'confmanager/tpl/descriptionHeader.php';
        echo '<div class="description_wrapper" id="description">';
        echo $this->helper->render($description);
        echo '</div>';
    }
}