<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbTag;

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

	public function parse($arguments = [])
	{
		$this->url = $this->readMainArgument($arguments);
	}

	public function render()
	{
		$src = htmlspecialchars($this->url);

		return <<<HTML
<audio controls>
<source src="{$src}" />
</audio>
HTML;
	}
}
