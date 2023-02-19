<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\Util\VueUtil;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\view\bbcode\BbHelper;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * Peiling
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [peiling=2]
 * @example [peiling]2[/peiling]
 */
class BbPeiling extends BbTag
{
	/**
	 * @var Peiling
	 */
	private $peiling;
	/**
	 * @var PeilingenRepository
	 */
	private $peilingenRepository;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var NormalizerInterface
	 */
	private $normalizer;

	public function __construct(
		NormalizerInterface $normalizer,
		PeilingenRepository $peilingenRepository
	) {
		$this->peilingenRepository = $peilingenRepository;
		$this->normalizer = $normalizer;
	}

	public static function getTagName()
	{
		return 'peiling';
	}
	public function isAllowed()
	{
		return $this->peiling->magBekijken();
	}

	public function renderLight()
	{
		$url = '#/peiling/' . urlencode($this->id);
		return BbHelper::lightLinkBlock(
			'peiling',
			$url,
			$this->peiling->titel,
			$this->peiling->beschrijving
		);
	}

	public function render()
	{
		return VueUtil::vueComponent('peiling', [
			'settings' => $this->normalizer->normalize($this->peiling, 'json', [
				'groups' => 'vue',
			]),
		]);
	}

	/**
	 * @param string|null $peiling_id
	 * @return Peiling
	 * @throws BbException
	 */
	private function getPeiling($peiling_id): Peiling
	{
		$peiling = $this->peilingenRepository->getPeilingById($peiling_id);
		if (!$peiling) {
			throw new BbException(
				'[peiling] Er bestaat geen peiling met (id:' . (int) $peiling_id . ')'
			);
		}

		return $peiling;
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
		$this->peiling = $this->getPeiling($this->id);
	}

	public function getId()
	{
		return $this->id;
	}
}
