<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\view\mededelingen\MededelingenView;

/**
 * Deze methode kan de belangrijkste mededelingen (doorgaans een top3) weergeven.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [mededelingen=top3]
 * @example [mededelingen]top3[/mededelingen]
 */
class BbMededelingen extends BbTag {

	public function getTagName() {
		return 'mededelingen';
	}

	public function parseLight($arguments = []) {
		$type = $this->getArgument($arguments);
		if ($type == '') {
			return '[mededelingen] Geen geldig mededelingenblok.';
		}
		return $this->lightLinkBlock('mededelingen', '/mededelingen', 'Mededelingen', 'Bekijk de laatste mededelingen');
	}

	public function parse($arguments = []) {
		$type = $this->getArgument($arguments);
		if ($type == '') {
			return '[mededelingen] Geen geldig mededelingenblok.';
		}
		$MededelingenView = new MededelingenView(0);
		switch ($type) {
			case 'top3nietleden': //lekker handig om dit intern dan weer anders te noemen...
				return $MededelingenView->getTopBlock('nietleden');
			case 'top3leden':
				return $MededelingenView->getTopBlock('leden');
			case 'top3oudleden':
				return $MededelingenView->getTopBlock('oudleden');
		}
		return '[mededelingen] Geen geldig type (' . htmlspecialchars($type) . ').';
	}
}
