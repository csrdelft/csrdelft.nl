<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
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
class BbUrl extends BbTag {

	public function getTagName() {
		return ['url', 'rul'];
	}

	public function parseLight($arguments = []) {
		$content = $this->getContent();
		$url = $this->getUrl($arguments, $content);
		return BbHelper::lightLinkInline($this->env, 'url', $url, $content);
	}

	public function parse($arguments = []) {
		$content = $this->getContent();
		$url = $this->getUrl($arguments, $content);
		return external_url($url, $content);
	}

	/**
	 * @param $arguments
	 * @param string|null $content
	 * @return string|null
	 */
	private function getUrl($arguments, $content) {
		if (isset($arguments['url'])) { // [url=
			$url = $arguments['url'];
		} elseif (isset($arguments['rul'])) { // [rul=
			$url = $arguments['rul'];
		} else { // [url][/url]
			$url = $content;
		}
		return $url;
	}
}
