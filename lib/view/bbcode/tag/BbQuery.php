<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\SavedQuery;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\SavedQueryContent;

/**
 * Deze methode kan resultaten van query's die in de database staan printen in een
 * tabelletje.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [query=1]
 * @example [query]1[/query]
 */
class BbQuery extends BbTag {

	public static function getTagName() {
		return 'query';
	}

	public function renderLight() {
		$this->assertId($this->content);
		$sqc = new SavedQueryContent(new SavedQuery($this->content));
		$url = '/tools/query?id=' . urlencode($this->content);
		return BbHelper::lightLinkBlock('query', $url, $sqc->getModel()->getBeschrijving(), $sqc->getModel()->count() . ' regels');
	}

	/**
	 * @param int $queryID
	 * @throws BbException
	 */
	private function assertId(int $queryID) {
		if ($queryID == 0) {
			throw new BbException('[query] Geen geldig query-id opgegeven');
		}
	}

	public function render() {
		$this->assertId($this->content);
		$sqc = new SavedQueryContent(new SavedQuery($this->content));
		return $sqc->render_queryResult();
	}

	/**
	 * @param array $arguments
	 * @return mixed
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		$this->content = (int)$this->content;
	}
}
