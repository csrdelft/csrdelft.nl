<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
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
	 * Stiekem hebben we helemaal geen leden
	 * @return GroepLid[]|ArrayCollection
	 */
	public function getLeden()
	{
		$profielRepository = ContainerFacade::getContainer()->get(
			ProfielRepository::class
		);
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$model = $em->getRepository(GroepLid::class);
		$leden = [];

		foreach (
			$profielRepository->findBy(['lidjaar' => $this->lidjaar])
			as $profiel
		) {
			/** @var GroepLid $lid */
			$lid = $model->nieuw($this, $profiel->uid);
			$lid->doorUid = null;
			$lid->doorProfiel = null;
			$lid->lidSinds = date_create_immutable(
				$profiel->lidjaar . '-09-01 00:00:00'
			);
			$leden[] = $lid;
		}
		return new ArrayCollection($leden);
	}

	public function getUrl()
	{
		return '/groepen/lichtingen/' . $this->lidjaar;
	}
}
