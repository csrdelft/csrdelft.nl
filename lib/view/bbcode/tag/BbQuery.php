<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\model\SavedQuery;
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

	public function getTagName() {
		return 'query';
	}

	public function parseLight($arguments = []) {
		$queryID = $this->getQueryID($arguments);
		if ($queryID != 0) {
			$sqc = new SavedQueryContent(new SavedQuery($queryID));
			$url = '/tools/query.php?id=' . urlencode($queryID);
			return $this->lightLinkBlock('query', $url, $sqc->getModel()->getBeschrijving(), $sqc->getModel()->count() . ' regels');
		} else {
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
	}

	private function getQueryID($arguments) {
		if (isset($arguments['query'])) {
			$queryID = $arguments['query'];
		} else {
			$queryID = $this->getContent();
		}
		return (int)$queryID;
	}

	public function parse($arguments = []) {
		$queryID = $this->getQueryID($arguments);
		if ($queryID != 0) {
			$sqc = new SavedQueryContent(new SavedQuery($queryID));
			return $sqc->render_queryResult();
		} else {
			return '[query] Geen geldig query-id opgegeven.<br />';
		}
	}
}
