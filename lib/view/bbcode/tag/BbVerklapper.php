<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbVerklapper extends BbTag {

	public static function getTagName() {
		return ['spoiler', 'verklapper'];
	}

	public function renderLight() {
		$content = str_replace('[br]', '<br />', $this->content);
		return '<a class="bb-tag-spoiler" href="#/verklapper/' . urlencode($content) . '">Toon verklapper</a>';
	}

	public function render() {
		$id = uniqid_safe('verklapper_');

		return <<<HTML
<div class="card">
	<a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#$id">Verklapper</a>
	<div id="$id" class="collapse"><div class="card-body">$this->content</div></div>
</div>
HTML;
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
	}
}
