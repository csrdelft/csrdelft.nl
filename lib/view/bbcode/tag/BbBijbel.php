<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\model\instellingen\LidInstellingenModel;
use CsrDelft\view\bbcode\BbHelper;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBijbel extends BbTag {

	public function getTagName() {
		return 'bijbel';
	}

	public function parseLight($arguments = []) {
		list($stukje, $link) = $this->getLink($arguments);
		return BbHelper::lightLinkInline($this->env, 'bijbel', $link, $stukje);
	}

	public function parse($arguments = []) {
		list($stukje, $link) = $this->getLink($arguments);
		return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
	}

	/**
	 * @param $arguments
	 * @return array
	 */
	private function getLink($arguments): array {
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
			$vertaling1 = lid_instelling('algemeen', 'bijbel');
		}
		$link = 'https://www.debijbel.nl/bijbel/' . urlencode($vertaling1) . '/' . urlencode($stukje);
		return array($stukje, $link);
	}
}
