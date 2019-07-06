<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\bbcode\BbHelper;

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
		return BbHelper::lightLinkInline($this->env, 'lid', '/profiel/' . $profiel->uid, $profiel->getNaam('user'));
	}

	/**
	 * @param $arguments
	 * @return \CsrDelft\model\entity\profiel\Profiel|false
	 * @throws BbException
	 */
	private function getProfiel($arguments) {
		$uid = $this->getArgument($arguments);
		$profiel = ProfielModel::get($uid);

		if (!$profiel) {
			throw new BbException('[lid] ' . htmlspecialchars($uid) . '] &notin; db.');
		}

		return $profiel;
	}

	public function parse($arguments = []) {
		$profiel = $this->getProfiel($arguments);
		return $profiel->getLink('user');
	}

}
