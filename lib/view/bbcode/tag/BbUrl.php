<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\common\Util\UrlUtil;
use CsrDelft\view\bbcode\BbHelper;

/**
 * URL
 *
 * @param String $arguments ['url'] URL waarnaar gelinkt wordt
 * @since 27/03/2019
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @example [url]https://csrdelft.nl[/url]
 * @example [url=https://csrdelft.nl]Stek[/url]
 */
class BbUrl extends BbTag
{
	public $url;

	public static function getTagName(): array
	{
		return ['url', 'rul'];
	}

	public function parse($arguments = []): void
	{
		$this->url = $this->getUrl($arguments);
		if ($this->url == null) {
			$this->readContent([], false);
			$this->url = $this->getChildren()[0]->render();
		} else {
			$this->readContent();
		}
	}

	public function renderPreview(): string
	{
		return $this->getContent() . ' 🔗 ';
	}

	public function renderPlain(): string
	{
		return $this->getContent() . ' (' . $this->url . ')';
	}

	public function renderLight(): string
	{
		return BbHelper::lightLinkInline(
			$this->env,
			'url',
			$this->url,
			$this->getContent()
		);
	}

	public function render(): string
	{
		return UrlUtil::external_url($this->url, $this->getContent());
	}

	/**
	 * @param $arguments
	 * @return string|null
	 */
	private function getUrl($arguments)
	{
		$url = null;
		if (isset($arguments['url'])) {
			// [url=
			$url = $arguments['url'];
		} elseif (isset($arguments['rul'])) {
			// [rul=
			$url = $arguments['rul'];
		}
		return $url;
	}
}
