<?php

namespace CsrDelft\view\bbcode\tag;

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

	public function getTagName() {
		return 'youtube';
	}

	public function parseLight($arguments = []) {
		$id = $this->getArgument($arguments);
		$this->assertId($id);

		return BbHelper::lightLinkBlock('youtube', 'https://youtu.be/' . $id, 'YouTube video', '', 'https://img.youtube.com/vi/' . $id . '/0.jpg');
	}

	/**
	 * @param string|null $id
	 * @throws BbException
	 */
	private function assertId($id) {
		if (!preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {
			throw new BbException('[youtube] Geen geldig youtube-id (' . htmlspecialchars($id) . ')');
		}
	}

	public function parse($arguments = []) {
		$id = $this->getArgument($arguments);
		$this->assertId($id);

		$attributes['width'] = 570;
		$attributes['height'] = 360;
		$attributes['iframe'] = true;

		$attributes['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
		$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';

		$params = json_encode($attributes);

		return <<<HTML
<div class="bb-video">
	<div class="bb-video-preview" onclick="event.preventDefault();window.bbcode.bbvideoDisplay(this);" data-params='{$params}' title="Klik om de video af te spelen">
		<div class="play-button fa fa-play-circle-o fa-5x"></div>
		<div class="bb-img-loading" src="{$previewthumb}"></div>
	</div>
</div>
HTML;
	}
}
