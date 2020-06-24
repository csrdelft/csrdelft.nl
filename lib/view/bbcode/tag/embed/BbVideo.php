<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;

/**
 * Universele videotag, gewoon urls erin stoppen. Ik heb een poging
 * gedaan hem een beetje vergevingsgezind te laten zijn...
 *
 * Tot nu toe youtube, vimeo, dailymotion, 123video, godtube
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @author Jieter
 * @since 27/03/2019
 * @example [video]https://www.youtube.com/watch?v=Zo0LJrw5nCs[/video]
 * @example [video]Zo0LJrw5nCs[/video]
 * @example [video]https://vimeo.com/1582112[/video]
 */
class BbVideo extends BbTag {

	public static function getTagName() {
		return 'video';
	}

	public function renderLight() {
		list($src, $type) = $this->processVideo();

		return BbHelper::lightLinkBlock('video', $src, $type . ' video', '');
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render() {
		list($src, $type) = $this->processVideo();

		// Als er geen type is, laat dan het bestand zien.
		if ($type == null) {
			return <<<HTML
<video class="w-100" controls preload="metadata" src="$src"></video>
HTML;
		}

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
	 * @return array
	 * @throws BbException
	 */
	private function processVideo(): array {
		$content = $this->content;
		$matches = array();

		//match type and id
		if (strstr($content, 'youtube.com') || strstr($content, 'youtu.be')) {
			if (preg_match('#(?:youtube\.com/watch\?v=|youtu.be/)([0-9a-zA-Z\-_]{11})#', $content, $matches) > 0) {
				return ['//www.youtube-nocookie.com/embed/' . $matches[1] . '?modestbranding=1&hl=nl', 'YouTube'];
			}
			throw new BbException('Geen geldige YouTube url: ' . $content);
		} elseif (strstr($content, 'vimeo')) {
			if (preg_match('#vimeo\.com/(?:clip\:)?(\d+)#', $content, $matches) > 0) {
				return ['//player.vimeo.com/video/' . $matches[1], 'Vimeo'];
			}

			throw new BbException('Geen geldige Vimeo url: ' . $content);
		} elseif (strstr($content, 'dailymotion')) {
			if (preg_match('#dailymotion\.com/video/([a-z0-9]+)#', $content, $matches) > 0) {
				return ['//dailymotion.com/embed/video/' . $matches[1], 'DailyMotion'];
			}

			throw new BbException('Geen geldige DailyMotion url: ' . $content);
		}

		return [$content, null];
	}

	/**
	 * @param $type
	 * @param $id
	 * @param $content
	 * @throws BbException
	 */
	private function assertId($type, $id, $content) {
		if (empty($type) || empty($id)) {
			throw new BbException('[video] Niet-ondersteunde video-website (' . htmlspecialchars($content) . ')');
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
