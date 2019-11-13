<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\model\entity\Plaatje;

class BbPlaatje extends BbImg {

	public static function getTagName()
	{
		return 'plaatje';
	}


	public function isAllowed()
	{
		return mag("P_LOGGED_IN");
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		parent::parse($arguments);
		$plaatje = new Plaatje($this->content);
		if (!$plaatje->exists()) {
			throw new BbException("Plaatje bestaat niet");
		}
		$this->content = $plaatje->getUrl();
	}
}
