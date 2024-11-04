<?php

namespace CsrDelft\view\bbcode\tag\embed;

use CsrDelft\bb\BbTag;

class BbAudio extends BbTag
{
	/**
	 * @var string
	 */
	public $url;

	/**
	 * @return string[]
	 *
	 * @psalm-return list{'audio', 'geluid'}
	 */
	public static function getTagName()
	{
		return ['audio', 'geluid'];
	}

	/**
	 * @return void
	 */
	public function parse($arguments = [])
	{
		$this->url = $this->readMainArgument($arguments);
	}

	/**
	 * @return string
	 */
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
