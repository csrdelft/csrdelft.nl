<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\Lib\Bb\BbTag;

class BbAudio extends BbTag
{
	/**
	 * @var string
	 */
	public $url;

	public static function getTagName()
	{
		return ['audio', 'geluid'];
	}

	public function parse($arguments = []): void
	{
		$this->url = $this->readMainArgument($arguments);
	}

	public function render(): string
	{
		$src = htmlspecialchars($this->url);

		return <<<HTML
<audio controls>
<source src="{$src}" />
</audio>
HTML;
	}
}
