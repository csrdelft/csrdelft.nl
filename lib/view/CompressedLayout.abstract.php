<?php

namespace CsrDelft\view;

use Stash\Driver\FileSystem;
use Stash\Pool;

/**
 * CompressedLayout.abstract.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Gebruikt .ini files voor stylesheets en scripts per module en layout.
 *
 * @see htdocs/tools/css.php
 * @see htdocs/tools/js.php
 */
abstract class CompressedLayout extends HtmlPage {
    private $layout;

    public function __construct($layout, View $body, $titel) {
        parent::__construct($body, $titel);
        $this->layout = $layout;
    }

    protected function getLayout() {
        return $this->layout;
    }

    /**
     * Controleer de cache.
     *
     * @param $layout
     * @param $module
     * @param $extension
     *
     * @return string   Hash voor de timestamp van de laatste cache.
     */
    public function checkCache($layout, $module, $extension) {
        $driver = new FileSystem(['path' => DATA_PATH . 'assets/']);
        $cachePool = new Pool($driver);
        $item = $cachePool->getItem(sprintf('/%s/%s/%s', $extension, $layout, $module));

        if ($item->isHit()) {
            return hash('crc32', $item->getCreation()->format('U'));
        } else {
            // Er bestaat geen cache, uitzondering.
            return hash('crc32', date('U'));
        }
    }

    /**
     * Add compressed css en js to page for module.
     *
     * @param string $module
     */
    public function addCompressedResources($module) {
        $sheet = sprintf('/styles/%s/%s/%s.css',
            $this->checkCache($this->layout, $module, 'css'),
            $this->layout,
            $module
        );
        parent::addStylesheet($sheet, true);

        $script = sprintf('/scripts/%s/%s/%s.js',
            $this->checkCache($this->layout, $module, 'js'),
            $this->layout,
            $module
        );
        parent::addScript($script, true);
    }


}
