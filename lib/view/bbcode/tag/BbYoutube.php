<?php

namespace CsrDelft\view\bbcode\tag;

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
		// TODO: Implement getTagName() method.
	}

	public function parseLight($arguments = []) {
		$id = $this->getArgument($arguments);

		if (preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {
			return $this->lightLinkBlock('youtube', 'https://youtu.be/' . $id, 'YouTube video', '', 'https://img.youtube.com/vi/' . $id . '/0.jpg');
		} else {
			return '[youtube] Geen geldig youtube-id (' . htmlspecialchars($id) . ')';
		}
	}

	public function parse($arguments = []) {
		$id = $this->getArgument($arguments);
		if (preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {

			$attributes['width'] = 570;
			$attributes['height'] = 360;
			$attributes['iframe'] = true;

			$attributes['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
			$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';

			return $this->video_preview($attributes, $previewthumb);
		} else {
			return '[youtube] Geen geldig youtube-id (' . htmlspecialchars($id) . ')';
		}
	}

	/**
	 * @param $arguments
	 * @return string|null
	 */
	private function getArgument($arguments) {
		$id = $this->parser->parseArray(array('[/youtube]'), array());
		if (isset($arguments['youtube'])) { // [youtube=
			$id = $arguments['youtube'];
		}
		return $id;
	}
}
