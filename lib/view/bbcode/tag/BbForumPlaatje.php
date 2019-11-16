<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\model\entity\ForumPlaatje;
use CsrDelft\model\ForumPlaatjeModel;

class BbForumPlaatje extends BbImg {

	/**
	 * @var ForumPlaatje
	 */
	private $plaatje;

	public static function getTagName()
	{
		return 'plaatje';
	}


	public function isAllowed()
	{
		return mag("P_LOGGED_IN");
	}

	public function getLinkUrl()
	{
		return $this->plaatje->getUrl(false);
	}

	public function getSourceUrl()
	{
		return $this->plaatje->getUrl(true);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		$plaatje = ForumPlaatjeModel::getByKey($this->content);
		if (!$plaatje) {
			throw new BbException("Plaatje bestaat niet");
		}
		$this->plaatje = $plaatje;
		$this->arguments = $arguments;
	}
}
