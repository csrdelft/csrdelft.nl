<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\Lib\Bb\BbException;
use CsrDelft\Lib\Bb\BbTag;
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
class BbYoutube extends BbTag
{
	/**
	 * @var string
	 */
	public $id;

	public static function getTagName()
	{
		return 'youtube';
	}

	public function renderPreview(): string
	{
		return '📹';
	}

	public function renderLight(): string
	{
		$this->assertId($this->id);

		return BbHelper::lightLinkBlock(
			'youtube',
			"https://youtu.be/{$this->id}",
			'YouTube video',
			'',
			"https://img.youtube.com/vi/{$this->id}/0.jpg"
		);
	}

	/**
	 * @param string|null $id
	 * @throws BbException
	 */
	private function assertId($id)
	{
		if (!preg_match('/^[0-9a-zA-Z\-_]{11}$/', $id)) {
			throw new BbException(
				'[youtube] Geen geldig youtube-id (' . htmlspecialchars($this->id) . ')'
			);
		}
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render(): string
	{
		$this->assertId($this->id);

		$src =
			'//www.youtube-nocookie.com/embed/' .
			$this->id .
			'?modestbranding=1&hl=nl';

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
	public function parse($arguments = []): void
	{
		$this->id = $this->readMainArgument($arguments);
	}
}
