<?php

class ConfigManagerSingleLineCoreConfig extends ConfigManagerAbstractCascadeConfig {

    protected  function loadFile($fileName) {
        if (@!file_exists($fileName)) {
            return array();
        }
        $config = file($fileName);
        $config = array_map('trim', $config);
        $config = preg_replace('/^#.*/', '', $config);
        $config = str_replace('\\#', '#', $config);
        $config = array_filter($config);
        return $config;
    }

    public function display() {
        $configs = $this->readConfig();
        $default = $configs['default'];
        $local = $configs['local'];
        $configs = array_merge($default, $local);

        usort($configs, array($this->helper, '_sortHuman'));
        include DOKU_PLUGIN . 'confmanager/tpl/showConfigSingleLine.php';
    }

    public function save() {
        global $INPUT;
        $lines = $INPUT->arr('line');
        $config = $this->readConfig();
        $custom = $this->getCustomEntries($lines, $config['default']);

        $this->saveToFile($custom);
    }

    private function getCustomEntries($input, $default) {
        $save = array();
        foreach ($input as $line) {
            if (in_array($line, $default)) {
                continue;
            }
            $line = $this->prepareEntity($line);
            if ($line === '') {
                continue;
            }

            $save[] = $line;
        }

        return $save;
    }

    private function saveToFile($config) {
        global $config_cascade;
        if (!isset($config_cascade[$this->internalName]['local'])
            || count($config_cascade[$this->internalName]['local']) === 0) {
            msg($this->helper->getLang('no local file given'),-1);
            return;
        }

        $file = $config_cascade[$this->internalName]['local'][0];

        if (empty($config)) {
            if (!@unlink($file)) {
                msg($this->helper->getLang('cannot apply changes'), -1);
                return;
            }
            msg($this->helper->getLang('changes applied'), 1);
            return;
        }

        uksort($config, array($this->helper, '_sortConf'));
        $content = $this->helper->getCoreConfigHeader();
        foreach ($config as $item) {
            $content .= "$item\n";
        }

        file_put_contents($file, $content);
        msg($this->helper->getLang('changes applied'), 1);
    }


}