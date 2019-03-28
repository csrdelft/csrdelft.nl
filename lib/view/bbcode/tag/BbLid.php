<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\ProfielModel;
use CsrDelft\view\bbcode\CsrBbException;

/**
 * Geef een link weer naar het profiel van het lid-nummer wat opgegeven is.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [lid=0436]
 * @example [lid]0436[/lid]
 */
class BbLid extends BbTag {
	public function getTagName() {
		return 'lid';
	}

	public function parseLight($arguments = []) {
		$profiel = $this->getProfiel($arguments);
		return $this->lightLinkInline('lid', '/profiel/' . $profiel->uid, $profiel->getNaam('user'));
	}

	private function getProfiel($arguments) {
		$uid = $this->getArgument($arguments);
		$profiel = ProfielModel::get($uid);

		if (!$profiel) {
			throw new CsrBbException('[lid] ' . htmlspecialchars($uid) . '] &notin; db.');
		}

		return $profiel;
	}

	public function parse($arguments = []) {
		$profiel = $this->getProfiel($arguments);
		return $profiel->getLink('user');
	}

}
