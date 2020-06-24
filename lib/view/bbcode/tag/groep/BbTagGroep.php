<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\bb\BbException;
use CsrDelft\bb\BbTag;
use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbHelper;
use CsrDelft\view\groepen\GroepView;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
abstract class BbTagGroep extends BbTag {

	/**
	 * @var AbstractGroepenRepository
	 */
	private $model;
	/**
	 * @var SerializerInterface
	 */
	private $serializer;

	public function __construct(AbstractGroepenRepository $model, SerializerInterface $serializer) {
		$this->model = $model;
		$this->serializer = $serializer;
	}

	/**
	 * @return bool
	 * @throws BbException
	 */
	public function isAllowed() {
		return $this->getGroep()->mag(AccessAction::Bekijken);
	}

	/**
	 * @return AbstractGroep
	 * @throws BbException
	 */
	private function getGroep() {
		$this->content = (int)$this->content;
		$groep = $this->model->get($this->content);
		if (!$groep) {
			throw new BbException("Groep met id $this->content does not exist");
		}
		return $groep;
	}

	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
	}

	public function renderLight() {
		$groep = $this->getGroep();
		if ($groep) {
			return $this->groepLight($groep, 'ketzer', $this->getLidNaam());
		} else {
			$url = $this->model->getUrl();
			return ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($this->content) . ' bestaat niet. <a href="' . $url . '/beheren">Zoeken</a>';
		}
	}

	protected function groepLight(AbstractGroep $groep, $tag, $leden) {
		return BbHelper::lightLinkBlock($tag, $groep->getUrl(), $groep->naam, $groep->aantalLeden() . ' ' . $leden);
	}

	abstract public function getLidNaam();

	/**
	 * @return string
	 * @throws BbException
	 */
	public function render() {
		$groep = $this->getGroep();
		if (!$groep) {
			$url = $this->model->getUrl();
			throw new BbException(ucfirst($this->getTagName()) . ' met id=' . htmlspecialchars($this->content) . ' bestaat niet. <a href="' . $url . '/beheren">Zoeken</a>');
		}
		return $this->groep($groep);
	}

	protected function groep(AbstractGroep $groep) {
		if ($groep->versie == GroepVersie::V2()) {
			$uid = LoginService::getUid();
			$settings = [
				'mijn_uid' => $uid,
				'mijn_link' => ProfielRepository::getLink($uid),
				'aanmeld_url' => $groep->getUrl() . '/aanmelden2/' . $uid,
			];

			$groepJson = htmlspecialchars($this->serializer->serialize($groep, 'json', ['groups' => ['vue']]));

			return vsprintf('<groep class="vue-context" :groep="%s" :settings="%s"></groep>', [
				$groepJson,
				vue_encode($settings)
			]);
		}
		$view = new GroepView($groep, null, false, true);
		return $view->getHtml();
	}
}
