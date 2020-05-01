<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\KringenRepository;
use CsrDelft\repository\groepen\leden\VerticaleLedenRepository;
use CsrDelft\Orm\Entity\T;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Verticale.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\VerticalenRepository")
 * @ORM\Table("verticalen")
 */
class Verticale extends AbstractGroep {

	const LEDEN = VerticaleLedenRepository::class;

	/**
	 * Primary key
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $letter;

	/**
	 * @var VerticaleLid[]
	 * @ORM\OneToMany(targetEntity="VerticaleLid", mappedBy="groep")
	 */
	public $leden;

	// Stiekem hebben we helemaal geen leden.
	public function getLeden() {
		$leden = [];
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		/** @var Profiel $profielen */
		$profielen = $profielRepository->createQueryBuilder('p')
			->where('p.verticale = :verticale and p.status in (:lidstatus)')
			->setParameter('verticale', $this->letter)
			->setParameter('lidstatus', LidStatus::getLidLike())
			->getQuery()->getResult();
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$model = $em->getRepository($this->getLidType());
		foreach ($profielen as $profiel) {
			if ($profiel AND $profiel->verticale === $this->letter) {
				$lid = $model->nieuw($this, $profiel->uid);
				if ($profiel->verticaleleider) {
					$lid->opmerking = 'Leider';
				} elseif ($profiel->kringcoach) {
					$lid->opmerking = 'Kringcoach';
				}
				$lid->door_uid = null;
				$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
				$leden[] = $lid;
			}
		}
		return $leden;
	}

	public function getLidType() {
		return VerticaleLid::class;
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'letter' => [T::Char]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	public function getUrl() {
		return '/groepen/verticalen/' . $this->letter;
	}

	public function getKringen() {
		return ContainerFacade::getContainer()->get(KringenRepository::class)->getKringenVoorVerticale($this);
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::mag($action, $allowedAuthenticationMethods);
		}
		return false;
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::magAlgemeen($action, $allowedAuthenticationMethods);
		}
		return false;
	}

}
