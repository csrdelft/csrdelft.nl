<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;

/**
 * Image
 *
 * @param string optional $arguments['class'] Class attribute
 * @param string optional $arguments['float'] CSS float left or right
 * @param integer optional $arguments['w'] CSS width in pixels
 * @param integer optional $arguments['h'] CSS height in pixels
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [img class=special float=left w=20 h=50]URL[/img]
 */
class BbImg extends BbTag {

	/**
	 * @var array
	 */
	protected $arguments;
	/**
	 * @var mixed
	 */
	private $url;

	public static function getTagName() {
		return 'img';
	}

	public function render() {
		$url = $this->getSourceUrl();
		$arguments = $this->arguments;

		$style = '';
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars($arguments['class']);
		}
		if (isset($arguments['float'])) {
			switch ($arguments['float']) {
				case 'left':
					$style .= 'float:left;';
					break;
				case 'right':
					$style .= 'float:right;';
					break;
			}
		}
		$heeftBreedte = isset($arguments['w']) && $arguments['w'] > 10;
		$heeftHoogte = isset($arguments['h']) && $arguments['h'] > 10;

		if ($heeftBreedte) {
			$style .= 'width: ' . ((int)$arguments['w']) . 'px; ';
		}
		if ($heeftHoogte) {
			$style .= 'height: ' . ((int)$arguments['h']) . 'px;';
		}

		if ($this->env->mode == "light") {
			// Geef een standaard breedte op om te voorkomen dat afbeeldingen te breed worden.
			if (!$heeftBreedte && !$heeftHoogte) {
				$style .= 'width:500px;';
			}

			return '<img class="' . $class . '" src="' . $url . '" alt="' . htmlspecialchars($url) . '" style="' . $style . '" />';
		}
		return '<div class="bb-img-loading" bb-href= "' . $this->getLinkUrl() . '" src= "' . $url . '" title="' . htmlspecialchars($url) . '" style="' . $style . '"></div>';
	}

	public function getSourceUrl() {
		return $this->url;
	}

	public function getLinkUrl() {
		return $this->url;
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$url = $this->readMainArgument($arguments);
		$url = filter_var($url, FILTER_SANITIZE_URL);
		if (!$url || (!url_like($url) && !startsWith($url, '/'))) {
			throw new BbException("Wrong url ".$url);
		}
		$this->arguments = $arguments;
		$this->url = $url;
	}
}
