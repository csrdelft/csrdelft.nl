<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Verticale.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\groepen\VerticalenRepository::class
	)
]
class Verticale extends Groep
{
	/**
	 * Primary key
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[
		ORM\Column(
			type: 'string',
			unique: true,
			length: 1,
			options: ['fixed' => true]
		)
	]
	public $letter;

	/**
	 * Naam
	 * @var string
	 */
	#[Serializer\Groups(['datatable', 'log', 'vue'])]
	#[ORM\Column(type: 'stringkey', unique: true)]
	public $naam;

	// Stiekem hebben we helemaal geen leden.
	public function getLeden()
	{
		$leden = [];
		$container = ContainerFacade::getContainer();
		$profielRepository = $container->get(ProfielRepository::class);
		/** @var Profiel $profielen */
		$profielen = $profielRepository
			->createQueryBuilder('p')
			->where('p.verticale = :verticale and p.status in (:lidstatus)')
			->setParameter('verticale', $this->letter)
			->setParameter('lidstatus', LidStatus::getLidLike())
			->getQuery()
			->getResult();
		$em = $container->get('doctrine.orm.entity_manager');
		$model = $em->getRepository(GroepLid::class);
		foreach ($profielen as $profiel) {
			if ($profiel && $profiel->verticale === $this->letter) {
				$lid = $model->nieuw($this, $profiel->uid);
				if ($profiel->verticaleleider) {
					$lid->opmerking = 'Leider';
				} elseif ($profiel->kringcoach) {
					$lid->opmerking = 'Kringcoach';
				}
				$lid->doorUid = null;
				$lid->doorProfiel = null;
				$lid->lidSinds = date_create_immutable(
					$profiel->lidjaar . '-09-01 00:00:00'
				);
				$leden[] = $lid;
			}
		}
		return new ArrayCollection($leden);
	}

	public function getUrl()
	{
		return '/groepen/verticalen/' . $this->letter;
	}
}
