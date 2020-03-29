<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\model\entity\SavedQueryResult;
use CsrDelft\model\SavedQueryModel;
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

	/**
	 * @var SavedQueryResult
	 */
	private $query;
	/**
	 * @var SavedQueryModel
	 */
	private $savedQueryModel;

	public function __construct(SavedQueryModel $savedQueryModel) {
		$this->savedQueryModel = $savedQueryModel;
	}

	public static function getTagName() {
		return 'query';
	}

	public function isAllowed() {
		return $this->query->query->magBekijken();
	}

	public function renderLight() {
		$url = '/tools/query?id=' . urlencode($this->content);
		return BbHelper::lightLinkBlock('query', $url, $this->query->query->beschrijving, count($this->query->rows) . ' regels');
	}

	public function render() {
		$sqc = new SavedQueryContent($this->query);
		return $sqc->render_queryResult();
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
		$this->content = (int)$this->content;
		$this->assertId($this->content);
		$this->query = $this->savedQueryModel->loadQuery($this->content);
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
}
