<?php

namespace CsrDelft\view\bbcode\tag;

/**
 * Twitter widget
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param optional Integer $arguments['lines']
 * @param optional Integer $arguments['width'] Breedte
 * @param optional Integer $arguments['height'] Hoogte
 *
 * @example [twitter][/twitter]
 */
class BbTwitter extends BbTag {

	public function getTagName() {
		return 'twitter';
	}

	public function parseLight($arguments = []) {
		$content = $this->getContent();

		return $this->lightLinkBlock('twitter', 'https://twitter.com/' . $content, 'Twitter', 'Tweets van @' . $content);
	}

	public function parse($arguments = []) {
		$content = $this->getContent();

		// widget size
		$width = 580;
		$height = 300;
		if (isset($arguments['width']) && (int)$arguments['width'] > 100) {
			$width = (int)$arguments['width'];
		}
		if (isset($arguments['height']) && (int)$arguments['height'] > 100) {
			$height = (int)$arguments['height'];
		}

		$script = <<<HTML
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
HTML;

		if (preg_match('/status/', $content)) {
			return <<<HTML
<blockquote class="twitter-tweet" data-lang="nl" data-dnt="true" data-link-color="#0a338d">
	<a href="{$content}">Tweet op Twitter</a>
</blockquote>
{$script}
HTML;
		}

		if (startsWith($content, '@')) {
			$content = 'https://twitter.com/' . $content;
		}

		return <<<HTML
<a class="twitter-timeline" 
	 data-lang="nl" data-width="{$width}" data-height="{$height}" data-dnt="true" data-theme="light"data-link-color="#0a338d" 
	 href="https://twitter.com/{$content}">
	 	Tweets van {$content}
</a>
{$script}
HTML;

	}
}
