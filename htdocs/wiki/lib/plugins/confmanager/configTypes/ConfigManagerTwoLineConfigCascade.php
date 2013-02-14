<?php

class ConfigManagerTwoLineCascadeConfig extends ConfigManagerAbstractCascadeConfig {

    protected function loadFile($fileName) {
        return confToHash($fileName);
    }

    public function display() {
        $configs = $this->readConfig();
        $default = $configs['default'];
        $local = $configs['local'];
        $configs = array_merge($default, $local);

        uksort($configs, array($this->helper, '_sortHuman'));
        include DOKU_PLUGIN . 'confmanager/tpl/showConfigTwoLine.php';
    }

    public function save() {
        global $INPUT;
        $config = $this->readConfig();
        $keys = $INPUT->arr('keys');
        $values = $INPUT->arr('values');
        if (count($keys) !== count($values)) {
            msg('invalid save arguments', -1);
        }

        if (empty($keys)) {
            $lines = array();
        } else {
            $lines = array_combine($keys, $values);
        }

        $lines = array_merge($lines, $this->getNewValues());

        $custom = $this->getCustomEntries($lines, $config['default']);

        $this->saveToFile($custom);
        $this->handleSave($config['default']);
    }

    protected function handleSave() {}

    private function getCustomEntries($input, $default) {
        $save = array();
        foreach ($input as $key => $value) {

            if (array_key_exists($key, $default)) {
                if ($default[$key] === $value) {
                    continue;
                }
            }

            $key = $this->prepareEntity($key);
            $value = $this->prepareEntity($value);
            if ($key === '' || $value === '') {
                continue;
            }
            $save[$key] = $value;
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
        foreach ($config as $key => $value) {
            $content .= "$key\t$value\n";
        }

        file_put_contents($file, $content);
        msg($this->helper->getLang('changes applied'), 1);
    }

    private function getNewValues() {
        global $INPUT;
        $newKey = $INPUT->arr('newKey');
        $newValue = $INPUT->arr('newValue');
        if (count($newKey) !== count($newValue)) {
            return array();
        }

        return array_combine($newKey, $newValue);
    }
}