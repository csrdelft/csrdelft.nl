<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\groepen\GroepView;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
abstract class BbTagGroep extends BbTag
{
	/**
	 * @var GroepRepository
	 */
	private $model;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;
	/**
	 * @var string
	 */
	private $id;

	public function __construct(
		GroepRepository $model,
		SerializerInterface $serializer
	) {
		$this->model = $model;
		$this->serializer = $serializer;
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
		$groep = $this->model->get($this->id);
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
			$url = $this->model->getUrl();
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
	 * @return string
	 * @throws BbException
	 */
	public function render()
	{
		$groep = $this->getGroep();
		if (!$groep) {
			$url = $this->model->getUrl();
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

			$groepJson = htmlspecialchars(
				$this->serializer->serialize($groep, 'json', ['groups' => ['vue']])
			);

			return vsprintf(
				'<groep class="vue-context" :groep="%s" :settings="%s"></groep>',
				[$groepJson, vue_encode($settings)]
			);
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}
}
