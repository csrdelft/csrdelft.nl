<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Lichting.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\LichtingenRepository")
 */
class Lichting extends Groep
{
	/**
	 * Lidjaar
	 * @var int
	 * @ORM\Column(type="integer", unique=true)
	 * @Serializer\Groups({"datatable", "log", "vue"})
	 */
	public $lidjaar;

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @param null $soort
	 * @return bool
	 */
	public static function magAlgemeen(AccessAction $action, $allowedAuthenticationMethods = null, $soort = null)
	{
		return AccessAction::isBekijken($action);
	}

	/**
	 * Stiekem hebben we helemaal geen leden
	 * @return GroepLid[]|ArrayCollection
	 */
	public function getLeden()
	{
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$model = $em->getRepository(GroepLid::class);
		$leden = [];

		foreach ($profielRepository->findBy(['lidjaar' => $this->lidjaar]) as $profiel) {
			/** @var GroepLid $lid */
			$lid = $model->nieuw($this, $profiel->uid);
			$lid->door_uid = null;
			$lid->door_profiel = null;
			$lid->lidSinds = date_create_immutable($profiel->lidjaar . '-09-01 00:00:00');
			$leden[] = $lid;
		}
		return new ArrayCollection($leden);
	}

	public function getUrl()
	{
		return '/groepen/lichtingen/' . $this->lidjaar;
	}

	/**
	 * Read-only: generated group
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag(AccessAction $action, $allowedAuthenticationMethods = null)
	{
		return AccessAction::isBekijken($action);
	}

}
