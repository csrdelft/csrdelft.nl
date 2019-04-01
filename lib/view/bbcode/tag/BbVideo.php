<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\view\bbcode\CsrBbException;
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

	public function getTagName() {
		return 'video';
	}

	public function parseLight($arguments = []) {
		list($content, $params, $previewthumb, $type, $id) = $this->processVideo();
		$this->assertId($type, $id, $content);

		return $this->lightLinkBlock('video', $content, $type . ' video', '', $previewthumb);
	}

	public function parse($arguments = []) {
		list($content, $params, $previewthumb, $type, $id) = $this->processVideo();
		$this->assertId($type, $id, $content);

		return $this->video_preview($params, $previewthumb);
	}

	/**
	 * @return array
	 */
	private function processVideo(): array {
		$content = $this->getContent();

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
	 */
	private function assertId($type, $id, $content): void {
		if (empty($type) || empty($id)) {
			throw new CsrBbException('[video] Niet-ondersteunde video-website (' . htmlspecialchars($content) . ')');
		}
	}
}
