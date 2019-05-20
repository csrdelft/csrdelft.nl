<?php

namespace CsrDelft\view\bbcode\tag;

/**
 * URL
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param String $arguments ['url'] URL waarnaar gelinkt wordt
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
		return $this->lightLinkInline('url', $url, $content);
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
