<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\BbHelper;
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

	public static function getTagName() {
		return 'mededelingen';
	}
	public function isAllowed()
	{
		LoginModel::mag(P_LOGGED_IN);
	}

	public function renderLight() {
		$type = $this->content;
		$this->assertType($type);
		return BbHelper::lightLinkBlock('mededelingen', '/mededelingen', 'Mededelingen', 'Bekijk de laatste mededelingen');
	}

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render() {
		$type = $this->content;
		$this->assertType($type);
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

	/**
	 * @param string|null $type
	 * @throws BbException
	 */
	private function assertType($type) {
		if ($type == '') {
			throw new BbException('[mededelingen] Geen geldig mededelingenblok.');
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
	}
}
