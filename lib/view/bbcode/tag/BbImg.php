<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\Util\UrlUtil;

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
class BbImg extends BbTag
{
	/**
	 * @var array
	 */
	protected $arguments;
	/**
	 * @var mixed
	 */
	private $url;

	public static function getTagName()
	{
		return 'img';
	}

	public function render()
	{
		$arguments = $this->arguments;

		$style = '';
		$class = '';
		if (isset($arguments['class'])) {
			$class .= htmlspecialchars((string) $arguments['class']);
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
			$style .= 'width: ' . ((int) $arguments['w']) . 'px; ';
		}
		if ($heeftHoogte) {
			$style .= 'height: ' . ((int) $arguments['h']) . 'px;';
		}

		if ($this->env->mode == 'light') {
			// Geef een standaard breedte op om te voorkomen dat afbeeldingen te breed worden.
			if (!$heeftBreedte && !$heeftHoogte) {
				$style .= 'width:500px;';
			}

			return vsprintf(
				"<img class=\"%s\" src=\"%s\" alt=\"%s\" style=\"%s\" />",
				[
					$class,
					$this->getSourceUrl(),
					htmlspecialchars((string) $this->getSourceUrl()),
					$style,
				]
			);
		}

		return vsprintf(
			"<a href=\"%s\" data-fslightbox><span class=\"bb-img-loading\" data-src=\"%s\" style=\"%s\"></span></a>",
			[$this->getLinkUrl(), $this->getSourceUrl(), $style]
		);
	}

	public function getSourceUrl()
	{
		return $this->url;
	}

	public function getLinkUrl()
	{
		return $this->url;
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->url = filter_var(
			$this->readMainArgument($arguments),
			FILTER_SANITIZE_URL
		);

		if (
			!$this->url ||
			(!UrlUtil::url_like($this->url) && !str_starts_with($this->url, '/'))
		) {
			throw new BbException('Wrong url ' . $this->url);
		}

		$this->arguments = $arguments;
	}
}
