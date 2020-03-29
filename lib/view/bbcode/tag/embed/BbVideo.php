<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\formulier\UrlDownloader;

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
		list($content, $params, $previewthumb, $type, $id) = $this->processVideo();
		$this->assertId($type, $id, $content);

		return BbHelper::lightLinkBlock('video', $content, $type . ' video', '', $previewthumb);
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render() {
		list($content, $params, $previewthumb, $type, $id) = $this->processVideo();

		// Als er geen type is, laat dan het bestand zien.
		if ($type == null) {
			return <<<HTML
<video class="w-100" controls preload="metadata" src="$content"></video>
HTML;
		}

		$this->assertId($type, $id, $content);

		$params = json_encode($params);

		return <<<HTML
<div class="bb-video">
	<div class="bb-video-preview" onclick="event.preventDefault();window.bbcode.bbvideoDisplay(this);" data-params='{$params}' title="Klik om de video af te spelen">
		<div class="play-button fa fa-play-circle fa-5x"></div>
		<div class="bb-img-loading" src="{$previewthumb}"></div>
	</div>
</div>
HTML;
	}

	/**
	 * @return array
	 */
	private function processVideo(): array {
		$content = $this->content;

		$params = [];
		$params['width'] = 570;
		$params['height'] = 360;
		$params['iframe'] = true;
		$previewthumb = '';

		$type = null;
		$id = null;
		$matches = array();

		//match type and id
		if (strstr($content, 'youtube.com') || strstr($content, 'youtu.be')) {
			$type = 'YouTube';
			if (preg_match('#(?:youtube\.com/watch\?v=|youtu.be/)([0-9a-zA-Z\-_]{11})#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.youtube.com/embed/' . $id . '?autoplay=1';
			$previewthumb = 'https://img.youtube.com/vi/' . $id . '/0.jpg';
		} elseif (strstr($content, 'vimeo')) {
			$type = 'Vimeo';
			if (preg_match('#vimeo\.com/(?:clip\:)?(\d+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//player.vimeo.com/video/' . $id . '?autoplay=1';

			$videodataurl = 'https://vimeo.com/api/v2/video/' . $id . '.php';
			$data = '';
			$downloader = new UrlDownloader;
			if ($downloader->isAvailable()) {
				$data = $downloader->file_get_contents($videodataurl);
			}
			if ($data) {
				$data = unserialize($data);
				$previewthumb = $data[0]['thumbnail_medium'];
			}
		} elseif (strstr($content, 'dailymotion')) {
			$type = 'DailyMotion';
			if (preg_match('#dailymotion\.com/video/([a-z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['src'] = '//www.dailymotion.com/embed/video/' . $id . '?autoPlay=1';
			$previewthumb = 'https://www.dailymotion.com/thumbnail/video/' . $id;
		} elseif (strstr($content, 'godtube')) {
			$type = 'GodTube';
			if (preg_match('#godtube\.com/watch/\?v=([a-zA-Z0-9]+)#', $content, $matches) > 0) {
				$id = $matches[1];
			}
			$params['id'] = $id;
			$params['iframe'] = false;

			$previewthumb = 'https://www.godtube.com/resource/mediaplayer/' . $id . '.jpg';
		}

		return [$content, $params, $previewthumb, $type, $id];
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
