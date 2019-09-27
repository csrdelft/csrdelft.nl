<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;

/**
 * Twitter widget
 *
 * @param optional Integer $arguments['lines']
 * @param optional Integer $arguments['width'] Breedte
 * @param optional Integer $arguments['height'] Hoogte
 *
 * @since 27/03/2019
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @example [twitter][/twitter]
 */
class BbTwitter extends BbTag {

	public static function getTagName() {
		return 'twitter';
	}

	public function renderLight() {
		return BbHelper::lightLinkBlock('twitter', 'https://twitter.com/' . $this->content, 'Twitter', 'Tweets van @' . $this->content);
	}

	public function render() {
		// widget size
		$width = 580;
		$height = 300;


		$script = <<<HTML
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
HTML;

		if (preg_match('/status/', $this->content)) {
			return <<<HTML
<blockquote class="twitter-tweet" data-lang="nl" data-dnt="true" data-link-color="#0a338d">
	<a href="{$this->content}">Tweet op Twitter</a>
</blockquote>
{$script}
HTML;
		}

		return <<<HTML
<a class="twitter-timeline" 
	 data-lang="nl" data-width="{$width}" data-height="{$height}" data-dnt="true" data-theme="light" data-link-color="#0a338d" 
	 href="https://twitter.com/{$this->content}">
	 	Tweets van {$this->content}
</a>
{$script}
HTML;

	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readContent([], false);
		if (startsWith($this->content, '@')) {
			$this->content = 'https://twitter.com/' . $this->content;
		}
		if (!preg_match('^https?://(www.)?twitter.com/', $this->content)) {
			throw new BbException("Not a valid twitter url");
		}
	}
}
