<?php


namespace CsrDelft\view\bbcode\tag;


use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;

class BbForum extends BbTag {
	public $num = 3;
	/**
	 * @var \CsrDelft\model\entity\forum\ForumDeel
	 */
	private $deel;

	public static function getTagName() {
		return 'forum';
	}
	public function isAllowed()
	{
		return $this->deel->magLezen();
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function render() {
		if (!LoginModel::mag(P_LOGGED_IN)) {
			return '';
		}
		$deel = $this->content;
		ForumDradenModel::instance()->setAantalPerPagina($this->num);

		return view('forum.bb', [
			'deel' => $this->deel,
		])->getHtml();
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		if (isset($arguments['num'])) {
			$this->num = (int) $arguments['num'];
		}
		switch($this->content) {
			case 'recent':
				$this->deel = ForumDelenModel::instance()->getRecent();
				break;
			case 'belangrijk':
				$this->deel = ForumDelenModel::instance()->getRecent(true);
				break;
			default:
				$this->deel = ForumDelenModel::instance()->get($this->content);
				break;
		}
	}
}
