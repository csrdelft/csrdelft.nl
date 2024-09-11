<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\entity\SavedQueryResult;
use CsrDelft\repository\SavedQueryRepository;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\SavedQueryContent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Deze methode kan resultaten van query's die in de database staan printen in een
 * tabelletje.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [query=1]
 * @example [query]1[/query]
 */
class BbQuery extends BbTag
{
	/**
	 * @var SavedQueryResult
	 */
	private $query;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(
		private readonly SavedQueryRepository $savedQueryRepository
	) {
	}

	public static function getTagName()
	{
		return 'query';
	}

	public function isAllowed()
	{
		return $this->query->query->magBekijken();
	}

	public function renderLight()
	{
		$url = '/tools/query?id=' . urlencode($this->id);
		return BbHelper::lightLinkBlock(
			'query',
			$url,
			$this->query->query->beschrijving,
			count($this->query->rows) . ' regels'
		);
	}

	public function render()
	{
		$sqc = new SavedQueryContent($this->query);
		return $sqc->render_queryResult();
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
		$this->id = (int) $this->id;
		$this->assertId($this->id);
		try {
			$this->query = $this->savedQueryRepository->loadQuery($this->id);
		} catch (AccessDeniedException) {
			throw new BbException('[query] Geen geldige query');
		}
	}

	/**
	 * @param int $queryID
	 * @throws BbException
	 */
	private function assertId(int $queryID)
	{
		if ($queryID == 0) {
			throw new BbException('[query] Geen geldig query-id opgegeven');
		}
	}
}
