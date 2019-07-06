<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/07/2019
 */
class BbForum extends BbTag {

	const DEFAULT_NUM = 3;
	const ATTRIBUTE_NUM = 'num';
	const ATTRIBUTE_FORUM = 'forum';
	const FORUM_RECENT = 'recent';
	const FORUM_BELANGRIJK = 'belangrijk';

	public function getTagName() {
		return 'forum';
	}

	/**
	 * [forum=belangrijk num=3]
	 * [forum=3 num=4] Specifiek subforum
	 * [forum=recent num=3] Alle subfora
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 * @throws \Exception
	 */
	public function parse($arguments = []) {
		$forum = isset($arguments[self::ATTRIBUTE_FORUM]) ? $arguments[self::ATTRIBUTE_FORUM] : self::FORUM_RECENT;
		$num = isset($arguments[self::ATTRIBUTE_NUM]) ? intval($arguments[self::ATTRIBUTE_NUM]) : self::DEFAULT_NUM;

		if ($forum == self::FORUM_RECENT) {
			ForumDradenModel::instance()->setHuidigePagina(1, 0);
			$deel = new ForumDeel();
			$deel->setForumDraden(ForumDradenModel::instance()->getRecenteForumDraden($num, false));
			$deel->titel = 'Recent gewijzigd';
			$locatie = '/forum/recent';
		} else if ($forum == self::FORUM_BELANGRIJK) {
			ForumDradenModel::instance()->setHuidigePagina(1, 0);
			$deel = new ForumDeel();
			$deel->setForumDraden(ForumDradenModel::instance()->getRecenteForumDraden($num, true));
			$deel->titel = 'Belangrijk recent gewijzigd';
			$locatie = '/forum/belangrijk';
		} else {
			ForumDradenModel::instance()->setHuidigePagina(1, (int) $forum);
			ForumDradenModel::instance()->setAantalPerPagina($num);
			$deel = ForumDelenModel::get((int) $forum);
			$locatie = '/forum/deel/' . ((int) $forum);
		}

		return view('forum.bb', [
			'deel' => $deel,
			'num' => $num,
			'locatie' => $locatie,
		])->getHtml();
	}
}
