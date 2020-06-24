<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;

/**
 * YouTube speler
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param String $arguments ['youtube'] YouTube id van 11 tekens
 *
 * @example [youtube]dQw4w9WgXcQ[/youtube]
 * @example [youtube=dQw4w9WgXcQ]
 */
class BbYoutube extends BbTag {

	public static function getTagName() {
		return 'youtube';
	}

	public function renderLight() {
		$this->assertId($this->content);

		return BbHelper::lightLinkBlock('youtube', 'https://youtu.be/' . $this->content, 'YouTube video', '', 'https://img.youtube.com/vi/' . $this->content . '/0.jpg');
	}

	/**
	 * @param string|null $id
	 * @throws BbException
	 */
	private function assertId($id) {
		if (!preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {
			throw new BbException('[youtube] Geen geldig youtube-id (' . htmlspecialchars($this->content) . ')');
		}
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render() {
		$this->assertId($this->content);

		$src = '//www.youtube-nocookie.com/embed/' . $this->content . '?modestbranding=1&hl=nl';

		return <<<HTML
<div class="bb-video">
<iframe
	width="560"
	height="315"
	src="$src"
	frameborder="0"
	allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
	allowfullscreen
></iframe>
</div>
HTML;
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
