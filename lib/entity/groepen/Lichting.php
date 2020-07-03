<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lichting.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\LichtingenRepository")
 * @ORM\Table("lichtingen", indexes={
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="familie", columns={"familie"}),
 *   @ORM\Index(name="status", columns={"status"}),
 * })
 */
class Lichting extends AbstractGroep {
	/**
	 * Lidjaar
	 * @var int
	 * @ORM\Column(type="integer", unique=true)
	 */
	public $lidjaar;

	/**
	 * @var LichtingsLid[]
	 * @ORM\OneToMany(targetEntity="CsrDelft\entity\groepen\LichtingsLid", mappedBy="groep")
	 */
	public $leden;

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @param null $soort
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null) {
		return $action === AccessAction::Bekijken;
	}

	/**
	 * Stiekem hebben we helemaal geen leden
	 * @return AbstractGroepLid[]|ArrayCollection
	 */
	public function getLeden() {
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$model = $em->getRepository($this->getLidType());
		$leden = [];

		foreach ($profielRepository->findBy(['lidjaar' => $this->lidjaar]) as $profiel) {
			/** @var LichtingsLid $lid */
			$lid = $model->nieuw($this, $profiel->uid);
			$lid->door_uid = null;
			$lid->door_profiel = null;
			$lid->lid_sinds = date_create_immutable($profiel->lidjaar . '-09-01 00:00:00');
			$leden[] = $lid;
		}
		return new ArrayCollection($leden);
	}

	public function getLidType() {
		return LichtingsLid::class;
	}

	public function getUrl() {
		return '/groepen/lichtingen/' . $this->lidjaar;
	}

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		return $action === AccessAction::Bekijken;
	}

}
