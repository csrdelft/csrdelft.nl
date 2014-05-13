<?php

require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerConfigType.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerUploadable.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerAbstractCascadeConfig.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerSingleLineConfigCascade.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerTwoLineConfigCascade.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerTwoLineLeftImageConfigCascade.php';
require_once DOKU_PLUGIN . 'confmanager/configTypes/ConfigManagerTwoLine.php';

class action_plugin_confmanager_registerconfig extends DokuWiki_Action_Plugin {
    var $helper;

    /**
     * Register its handlers with the dokuwiki's event controller
     * @param Doku_Event_Handler $controller
     */
    public function register(&$controller) {
        $controller->register_hook('CONFMANAGER_CONFIGFILES_REGISTER', 'BEFORE',  $this, 'addCoreConfigFiles', array());
    }

    public function addCoreConfigFiles(&$event, $param) {
        /*
        $event->data[] = ConfigManagerConfigFile::create('smileys')
            ->setImageFolder(DOKU_INC . 'lib/images/smileys/')
            ->setConfigName('Smileys');
        */

        $scheme = new ConfigManagerSingleLineCoreConfig('scheme');
        $scheme->setName($this->getLang('URL Schemes'));
        $scheme->setDescription($this->getDescription('scheme'));
        $event->data[] = $scheme;

        $wordBlock = new ConfigManagerSingleLineCoreConfig('wordblock');
        $wordBlock->setName($this->getLang('Blacklisting'));
        $wordBlock->setDescription($this->getDescription('wordblock'));
        $event->data[] = $wordBlock;

        $acronyms = new ConfigManagerTwoLineCascadeConfig('acronyms');
        $acronyms->setName($this->getLang('Acronyms'));
        $acronyms->setDescription($this->getDescription('acronyms'));
        $event->data[] = $acronyms;

        $entities = new ConfigManagerTwoLineCascadeConfig('entities');
        $entities->setName($this->getLang('Entity replacements'));
        $entities->setDescription($this->getDescription('entities'));
        $event->data[] = $entities;

        $mime = new ConfigManagerTwoLineLeftImageConfigCascade('mime', 'lib/images/fileicons/', 'png');
        $mime->setName($this->getLang('MIME configuration'));
        $mime->setDescription($this->getDescription('mime'));
        $event->data[] = $mime;

        $interWiki = new ConfigManagerTwoLineLeftImageConfigCascade('interwiki', 'lib/images/interwiki', 'gif,png');
        $interWiki->setName($this->getLang('InterWiki Links'));
        $interWiki->setDescription($this->getDescription('interwiki'));
        $event->data[] = $interWiki;
    }

    private function getDescription($id) {
        $fn = $this->localFN($id);
        if (!@file_exists($fn)) {
            return '';
        }
        $content = file_get_contents($fn);
        return $content;
    }
}
