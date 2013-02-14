<?php

abstract class ConfigManagerAbstractCascadeConfig implements ConfigManagerConfigType {
    private $name;
    protected $internalName;
    private $description;
    private $path;

    /**
     * @var helper_plugin_confmanager
     */
    protected $helper;

    abstract protected function loadFile($fileName);

    public function __construct($name) {
        $this->internalName = $name;
        $this->path = getConfigFiles($name);
        $this->helper = plugin_load('helper', 'confmanager');
    }

    protected function readConfig() {
        global $config_cascade;
        $config = array();

        foreach (array('default', 'local', 'protected') as $type) {
            $config[$type] = array();

            if (!isset($config_cascade[$this->internalName][$type])) {
                continue;
            }

            foreach ($config_cascade[$this->internalName][$type] as $file) {
                $config[$type] = array_merge($config[$type], $this->loadFile($file));
            }
        }

        return $config;
    }

    protected function prepareEntity($str) {
        $str = trim($str);
        $str = str_replace("\n", '', $str);
        $str = str_replace("\r", '', $str);
        $str = str_replace('#', '\\#', $str);
        return $str;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getPaths() {
        return $this->path;
    }
}
