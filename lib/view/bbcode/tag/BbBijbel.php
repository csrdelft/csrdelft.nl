<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\view\bbcode\BbHelper;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBijbel extends BbTag
{
	private $bijbel;
	private $vertaling;

	public function __construct(
		private readonly LidInstellingenRepository $lidInstellingenRepository
	) {
	}

	public static function getTagName()
	{
		return 'bijbel';
	}

	public function renderLight()
	{
		[$stukje, $link] = $this->getLink();
		return BbHelper::lightLinkInline($this->env, 'bijbel', $link, $stukje);
	}

	public function render()
	{
		[$stukje, $link] = $this->getLink();
		return '<a href="' . $link . '" target="_blank">' . $stukje . '</a>';
	}

	/**
	 * @param $arguments
	 * @return array
	 */
	private function getLink(): array
	{
		$content = $this->getContent();
		$stukje =
			$this->bijbel != null ? str_replace('_', ' ', $this->bijbel) : $content;

		$vertaling1 = $this->vertaling;
		if (
			!$this->lidInstellingenRepository->isValidValue(
				'algemeen',
				'bijbel',
				$vertaling1
			)
		) {
			$vertaling1 = null;
		}
		if ($vertaling1 === null) {
			$vertaling1 = InstellingUtil::lid_instelling('algemeen', 'bijbel');
		}
		$link =
			'https://www.debijbel.nl/bijbel/' .
			urlencode((string) $vertaling1) .
			'/' .
			urlencode($stukje);
		return [$stukje, $link];
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readContent();
		$this->bijbel = $arguments['bijbel'] ?? null;
		$this->vertaling = $arguments['vertaling'] ?? null;
	}
}
