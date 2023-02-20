<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\common\Util\VueUtil;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\groepen\GroepView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
abstract class BbTagGroep extends BbTag
{
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var NormalizerInterface
	 */
	private $normalizer;

	public function __construct(
		EntityManagerInterface $entityManager,
		Environment $twig,
		NormalizerInterface $normalizer
	) {
		$this->twig = $twig;
		$this->entityManager = $entityManager;
		$this->normalizer = $normalizer;
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return bool
	 * @throws BbException
	 */
	public function isAllowed()
	{
		return $this->getGroep()->mag(AccessAction::Bekijken());
	}

	/**
	 * @return Groep
	 * @throws BbException
	 */
	private function getGroep()
	{
		$this->id = (int) $this->id;
		$groep = $this->getRepository()->get($this->id);
		if (!$groep) {
			throw new BbException("Groep met id $this->id bestaat niet");
		}
		return $groep;
	}

	public function parse($arguments = [])
	{
		$this->id = $this->readMainArgument($arguments);
	}

	public function renderLight()
	{
		$groep = $this->getGroep();
		if ($groep) {
			return $this->groepLight($groep, 'ketzer', $this->getLidNaam());
		} else {
			$url = $this->getRepository()->getUrl();
			return vsprintf(
				"%s met id=%s bestaat niet. <a href=\"%s/beheren\">Zoeken</a>",
				[ucfirst($this->getTagName()), htmlspecialchars($this->id), $url]
			);
		}
	}

	protected function groepLight(Groep $groep, $tag, $leden)
	{
		return BbHelper::lightLinkBlock(
			$tag,
			$groep->getUrl(),
			$groep->naam,
			$groep->aantalLeden() . ' ' . $leden
		);
	}

	abstract public function getLidNaam();

	/**
	 * @return Groep|string
	 */
	abstract public function getEntityClass(): string;

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render()
	{
		$groep = $this->getGroep();
		if (!$groep) {
			$url = $this->getRepository()->getUrl();
			throw new BbException(
				vsprintf(
					"%s met id=%s bestaat niet. <a href=\"%s/beheren\">Zoeken</a>",
					[ucfirst($this->getTagName()), htmlspecialchars($this->id), $url]
				)
			);
		}
		return $this->groep($groep);
	}

	protected function groep(Groep $groep)
	{
		if ($groep->versie == GroepVersie::V2()) {
			$uid = LoginService::getUid();
			$settings = [
				'mijn_uid' => $uid,
				'mijn_link' => ProfielRepository::getLink($uid),
				'aanmeld_url' => $groep->getUrl() . '/aanmelden2/' . $uid,
			];

			return VueUtil::vueComponent('groep', [
				'groep' => $this->normalizer->normalize($groep, 'json', [
					'groups' => ['vue'],
				]),
				'settings' => $settings,
			]);
		}
		$view = new GroepView($this->twig, $groep, null, false, true);
		return $view->getHtml();
	}

	/**
	 * @return GroepRepository
	 */
	private function getRepository(): GroepRepository
	{
		$objectRepository = $this->entityManager->getRepository(
			$this->getEntityClass()
		);

		if ($objectRepository instanceof GroepRepository) {
			return $objectRepository;
		}

		throw new CsrException(
			'Entity verwijst niet naar een GroepRepository: ' .
				$this->getEntityClass()
		);
	}
}
