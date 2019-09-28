<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
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
	public static function getTagName() {
		return 'lid';
	}

	public function isAllowed()
	{
		LoginModel::mag(P_LEDEN_READ . "," . P_OUDLEDEN_READ);
	}

	public function renderLight() {
		$profiel = $this->getProfiel();
		return BbHelper::lightLinkInline($this->env, 'lid', '/profiel/' . $profiel->uid, $profiel->getNaam('user'));
	}

	/**
	 * @param $arguments
	 * @return \CsrDelft\model\entity\profiel\Profiel|false
	 * @throws BbException
	 */
	private function getProfiel() {
		$profiel = ProfielModel::get($this->content);

		if (!$profiel) {
			throw new BbException('[lid] ' . htmlspecialchars($this->content) . '] &notin; db.');
		}

		return $profiel;
	}

	public function render() {
			$profiel = $this->getProfiel();
			return $profiel->getLink('user');
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
