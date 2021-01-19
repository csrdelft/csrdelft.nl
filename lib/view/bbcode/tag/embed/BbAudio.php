<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbTag;

class BbAudio extends BbTag
{

	public static function getTagName()
	{
		return ['audio', 'geluid'];
	}

	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}

	public function render()
	{
		$src = htmlspecialchars($this->content);

		return <<<HTML
<audio controls>
<source src="{$src}" />
</audio>
HTML;
	}
}
