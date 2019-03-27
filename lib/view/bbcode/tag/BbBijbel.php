<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\LidInstellingenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBijbel extends BbTag {

	public function getTagName() {
		return 'bijbel';
	}

	public function parseLight($arguments = []) {
		$content = $this->getContent();
		if (isset($arguments['bijbel'])) { // [bijbel=
			$stukje = str_replace('_', ' ', $arguments['bijbel']);
		} else { // [bijbel][/bijbel]
			$stukje = $content;
		}
		if (isset($arguments['vertaling'])) {
			$vertaling = $arguments['vertaling'];
		} else {
			$vertaling = null;
		}
		$vertaling1 = $vertaling;
		if (!LidInstellingenModel::instance()->isValidValue('algemeen', 'bijbel', $vertaling1)) {
			$vertaling1 = null;
		}
		if ($vertaling1 === null) {
			$vertaling1 = LidInstellingenModel::get('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . urlencode($vertaling1) . '/' . urlencode($stukje);
		return $this->lightLinkInline('bijbel', $link, $stukje);
	}

	public function parse($arguments = []) {
		$content = $this->getContent();
		if (isset($arguments['bijbel'])) { // [bijbel=
			$stukje = str_replace('_', ' ', $arguments['bijbel']);
		} else { // [bijbel][/bijbel]
			$stukje = $content;
		}
		if (isset($arguments['vertaling'])) {
			$vertaling = $arguments['vertaling'];
		} else {
			$vertaling = null;
		}
		$vertaling1 = $vertaling;
		if (!LidInstellingenModel::instance()->isValidValue('algemeen', 'bijbel', $vertaling1)) {
			$vertaling1 = null;
		}
		if ($vertaling1 === null) {
			$vertaling1 = LidInstellingenModel::get('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . urlencode($vertaling1) . '/' . urlencode($stukje);
		return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
	}
}
