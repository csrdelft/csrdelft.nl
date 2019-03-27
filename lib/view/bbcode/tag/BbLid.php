<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\ProfielModel;

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
		$uid = $this->getArgument($arguments);
		$profiel = ProfielModel::get($uid);

		return $this->lightLinkInline('lid', '/profiel/' . $uid, $profiel->getNaam('user'));
	}

	public function parse($arguments = []) {
		$uid = $this->getArgument($arguments);
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			return '[lid] ' . htmlspecialchars($uid) . '] &notin; db.';
		}
		return $profiel->getLink('user');
	}

}
