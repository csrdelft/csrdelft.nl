<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\model\entity\ForumPlaatje;
use CsrDelft\model\ForumPlaatjeModel;

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
		$plaatje = ForumPlaatjeModel::getByKey($this->content);
		if (!$plaatje) {
			throw new BbException("Plaatje bestaat niet");
		}
		$this->content = $plaatje->getUrl();
	}
}
