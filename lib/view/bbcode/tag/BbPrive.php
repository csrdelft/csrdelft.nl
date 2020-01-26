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
	/**
	 * @var string
	 */
	private $permissie;

	public function isAllowed()
	{
		return LoginModel::mag($this->permissie);
	}

	public static function getTagName() {
		return 'prive';
	}

	public function render() {
		return '<span class="bb-prive bb-tag-prive">' . $this->content . '</span>';
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
		$this->permissie = $arguments['prive'] ?? 'P_LOGGED_IN';
	}
}
