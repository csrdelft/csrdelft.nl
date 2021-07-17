<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Select an entity based on primary key values in hidden input fields, supplied by remote data source.
 *
 * NOTE: support alleen entities met een enkele primary key.
 */
class DoctrineEntityField extends TextField {
	protected $type = 'hidden';
	/**
	 * @var string
	 */
	private $showValue;
	/**
	 * @var  DisplayEntity
	 */
	private $entity;
	/**
	 * @var string
	 */
	private $idField;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var string
	 */
	private $entityType;
	/**
	 * @var string
	 */
	public $suggestieIdField = 'id';
	/**
	 * @var string
	 */
	private $url;

	/**
	 * EntityField constructor.
	 * @param $name string Prefix van de input
	 * @param DisplayEntity|null $value
	 * @param $description string Beschrijvijng van de input
	 * @param $type string|DisplayEntity
	 * @param $url string Url waar aanvullingen te vinden zijn
	 */
	public function __construct($name, $value, $description, $type, $url) {
		if (!is_a($type, DisplayEntity::class, true)) {
			throw new CsrException($type . ' moet DisplayEntity implementeren voor DoctrineEntityField');
		}

		$this->em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		$meta = $this->em->getClassMetadata($type);

		if (count($meta->getIdentifier()) !== 1) {
			throw new CsrException('DoctrineEntityField ondersteund geen entities met een composite primary key');
		}

		$this->idField = $meta->getIdentifier()[0];
		$this->entityType = $type;
		$this->entity = $value;
		$this->url = $url;
		$this->showValue = $value ? $value->getWeergave() : '';
		$this->origvalue = $value ? (string) $value->getId() : null;

		parent::__construct($name, $this->origvalue, $description);
	}

	public function getFormattedValue() {
		$id = $this->getValue();
		if ($id == null) {
			return null;
		}
		$this->entity = $this->em->getRepository($this->entityType)->find($id);
		$this->showValue = $this->entity->getWeergave();
		return $this->entity;
	}

	public function getName() {
		return $this->name;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}

		return $this->error === '';
	}

	public function getHtml() {
		$config = [
			'name' => $this->getName(),
			'id' => $this->getId(),
			'url' => $this->url,
			'valueShow' => $this->showValue,
			'valueId' => $this->value,
			'idField' => $this->suggestieIdField,
		];

		$configString = vue_encode($config);

		return parent::getHtml()
			. "<div data-entity-field=\"$configString\"></div>";
	}
}
