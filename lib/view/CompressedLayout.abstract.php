<?php

namespace CsrDelft\view;

use Stash\Driver\FileSystem as FileSystemDriver;
use Stash\Pool as CachePool;

/**
 * CompressedLayout.abstract.php
 *
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Gebruikt .ini files voor stylesheets en scripts per module en layout.
 *
 * @see htdocs/tools/css.php
 * @see htdocs/tools/js.php
 */
abstract class CompressedLayout extends HtmlPage {
	/**
	 * @var string
	 */
	private $layout;

	/**
	 * CompressedLayout constructor.
	 *
	 * @param string $layout
	 * @param View $body
	 * @param string $titel
	 */
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
	public function cacheHash($layout, $module, $extension) {
		$driver = new FileSystemDriver(['path' => DATA_PATH . 'assets/']);
		$cachePool = new CachePool($driver);
		$item = $cachePool->getItem(sprintf('/%s/%s/%s', $extension, $layout, $module));

		if ($item->isHit()) {
			return hash('crc32', $item->getCreation()->format('U'));
		} else {
			// Er bestaat geen cache, alleen nadat cache geleegd is.
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
			$this->cacheHash($this->getLayout(), $module, 'css'),
			$this->getLayout(),
			$module
		);
		parent::addStylesheet($sheet, true);

		$script = sprintf('/scripts/%s/%s/%s.js',
			$this->cacheHash($this->getLayout(), $module, 'js'),
			$this->getLayout(),
			$module
		);
		parent::addScript($script, true);
	}
}
