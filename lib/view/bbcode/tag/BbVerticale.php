<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\model\groepen\VerticalenModel;
use CsrDelft\model\security\LoginModel;

/**
 * Geeft een link naar de verticale.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [verticale]A[/verticale]
 * @example [verticale=A]
 */
class BbVerticale extends BbTag {

	public static function getTagName() {
		return 'verticale';
	}
	public function isAllowed()
	{
		return LoginModel::mag(P_LOGGED_IN);
	}

	public function render() {
		try {
			$verticale = VerticalenModel::get($this->content);
			return '<a href="/verticalen#' . $verticale->letter . '">' . $verticale->naam . '</a>';
		} catch (CsrException $e) {
			return 'Verticale met letter=' . htmlspecialchars($this->content) . ' bestaat niet. <a href="/verticalen">Zoeken</a>';
		}
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
