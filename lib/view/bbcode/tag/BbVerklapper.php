<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbVerklapper extends BbTag
{
	public static function getTagName()
	{
		return ['spoiler', 'verklapper'];
	}

	public function renderLight()
	{
		$content = str_replace('[br]', '<br />', $this->getContent());
		return '<a class="bb-tag-spoiler" href="#/verklapper/' .
			urlencode($content) .
			'">Toon verklapper</a>';
	}

	public function render()
	{
		$id = uniqid_safe('verklapper_');

		return <<<HTML
<div class="card">
	<a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#$id">Verklapper</a>
	<div id="$id" class="collapse"><div class="card-body">{$this->getContent()}</div></div>
</div>
HTML;
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
	}
}
