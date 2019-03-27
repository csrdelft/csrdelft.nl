<?php

namespace CsrDelft\view\bbcode\tag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbVerklapper extends BbTag {

	public function getTagName() {
		return ['spoiler', 'verklapper'];
	}

	public function parseLight($arguments = []) {
		$content = $this->getContent();
		$content = str_replace('[br]', '<br />', $content);
		return '<a class="bb-tag-spoiler" href="#/verklapper/' . urlencode($content) . '">Toon verklapper</a>';
	}

	public function parse($arguments = []) {
		$content = $this->getContent();

		$id = uniqid_safe('verklapper_');

		return <<<HTML
<div class="card">
	<a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#$id">Verklapper</a>
	<div id="$id" class="collapse"><div class="card-body">$content</div></div>
</div>
HTML;
	}
}
