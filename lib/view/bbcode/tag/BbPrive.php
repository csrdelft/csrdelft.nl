<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\model\security\LoginModel;

/**
 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 *
 * @example [prive]Persoonsgegevens[/prive]
 * @example [prive=commissie:PubCie]Tekst[/prive]
 */
class BbPrive extends BbTag {
	public function getTagName() {
		return 'prive';
	}

	public function parse($arguments = []) {
		if (isset($arguments['prive'])) {
			$permissie = $arguments['prive'];
		} else {
			$permissie = P_LOGGED_IN;
		}
		if (!LoginModel::mag($permissie)) {
			$this->parser->bb_mode = false;
			$forbidden = ['prive'];
		} else {
			$forbidden = [];
		}
		// content moet altijd geparsed worden, anders blijft de inhoud van de tag gewoon staan
		$content = '<span class="bb-prive bb-tag-prive">' . $this->getContent($forbidden) . '</span>';
		if (!LoginModel::mag($permissie)) {
			$content = '';
			$this->parser->bb_mode = true;
		}

		return $content;
	}
}
