<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\repository\peilingen\PeilingenRepository;
use CsrDelft\view\bbcode\BbHelper;
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
class BbPeiling extends BbTag {

	/**
	 * @var Peiling
	 */
	private $peiling;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;
	/**
	 * @var PeilingenRepository
	 */
	private $peilingenRepository;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(SerializerInterface $serializer, PeilingenRepository $peilingenRepository, Environment $twig) {
		$this->serializer = $serializer;
		$this->peilingenRepository = $peilingenRepository;
		$this->twig = $twig;
	}

	public static function getTagName() {
		return 'peiling';
	}
	public function isAllowed()
	{
		return $this->peiling->magBekijken();
	}

	public function renderLight() {
		$url = '#/peiling/' . urlencode($this->content);
		return BbHelper::lightLinkBlock('peiling', $url, $this->peiling->titel, $this->peiling->beschrijving);
	}

	public function render() {
		return $this->twig->render('peilingen/peiling.html.twig', [
			'peiling' => $this->serializer->serialize($this->peiling, 'json', ['groups' => 'vue']),
		]);
	}

	/**
	 * @param string|null $peiling_id
	 * @return Peiling
	 * @throws BbException
	 */
	private function getPeiling($peiling_id): Peiling {
		$peiling = $this->peilingenRepository->getPeilingById($peiling_id);
		if (!$peiling) {
			throw new BbException('[peiling] Er bestaat geen peiling met (id:' . (int)$peiling_id . ')');
		}

		return $peiling;
	}

	/**
	 * @param array $arguments
	 * @throws BbException
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		$this->peiling = $this->getPeiling($this->content);
	}
}
