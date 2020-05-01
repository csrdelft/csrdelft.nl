<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\leden\LichtingLedenRepository;
use CsrDelft\Orm\Entity\T;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lichting.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\LichtingenRepository")
 */
class Lichting extends AbstractGroep {

	const LEDEN = LichtingLedenRepository::class;

	/**
	 * Lidjaar
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $lidjaar;

	/**
	 * @var LichtingsLid[]
	 * @ORM\OneToMany(targetEntity="CsrDelft\entity\groepen\LichtingsLid", mappedBy="groep")
	 */
	public $leden;

	// Stiekem hebben we helemaal geen leden
	public function getLeden() {
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');
		$model = $em->getRepository($this->getLidType());

		foreach ($profielRepository->findBy(['lidjaar' => $this->lidjaar]) as $profiel) {
				$lid = $model->nieuw($this->lidjaar, $profiel->uid);
				$lid->door_uid = null;
				$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
				$leden[] = $lid;
		}
		return $this->leden;
	}

	public function getLidType() {
		return LichtingsLid::class;
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lidjaar' => array(T::Integer)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lichtingen';

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

	/**
	 * Read-only: generated group
	 * @param $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		return $action === AccessAction::Bekijken;
	}

}
