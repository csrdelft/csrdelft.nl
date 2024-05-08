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
 *
 * @see /assets/js/lib/formulier.ts#initDoctrineField
 */
class DoctrineEntityField extends TextField
{
	/**
	 * @var string
	 */
	public $suggestieIdField = 'id';
	/**
	 * @var string
	 */
	private $show_value;
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
	private $url;

	/**
	 * EntityField constructor.
	 * @param $name string Prefix van de input
	 * @param DisplayEntity|null $value
	 * @param $description string Beschrijvijng van de input
	 * @param $type DisplayEntity|string
	 * @param $url string Url waar aanvullingen te vinden zijn
	 */
	public function __construct($name, $value, $description, $type, $url)
	{
		if (!is_a($type, DisplayEntity::class, true)) {
			throw new CsrException(
				$type . ' moet DisplayEntity implementeren voor DoctrineEntityField'
			);
		}
		$this->em = ContainerFacade::getContainer()->get(
			'doctrine.orm.entity_manager'
		);

		$meta = $this->em->getClassMetadata($type);

		if (count($meta->getIdentifier()) !== 1) {
			throw new CsrException(
				'DoctrineEntityField ondersteund geen entities met een composite primary key'
			);
		}

		$this->idField = $meta->getIdentifier()[0];
		$this->entityType = $type;
		$this->entity = $value ?? new $type();
		$this->show_value = $this->entity->getWeergave();
		$this->origvalue = (string) $this->entity->getId();

		parent::__construct(
			$name,
			$value ? (string) $value->getId() : null,
			$description
		);

		$this->css_classes[] = 'doctrine-field';

		$this->url = $url;
	}

	public function getFormattedValue()
	{
		$value = $this->getValue();
		if ($value == null) {
			return null;
		}
		$this->entity = $this->em->getRepository($this->entityType)->find($value);
		$this->show_value = $this->entity->getWeergave();
		return $this->entity;
	}

	public function getName()
	{
		return $this->name;
	}

	public function validate(): bool
	{
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}

		return $this->error === '';
	}

	public function getHtml()
	{
		$id = $this->getId() . '_' . $this->idField;

		$html =
			'<input data-url="' .
			$this->url .
			'" data-id-field="' .
			$id .
			'" data-suggestie-id-field="' .
			$this->suggestieIdField .
			'" name="' .
			$this->name .
			'_show" value="' .
			$this->entity->getWeergave() .
			'" origvalue="' .
			$this->entity->getWeergave() .
			'"' .
			$this->getInputAttribute([
				'type',
				'id',
				'class',
				'disabled',
				'readonly',
				'maxlength',
				'placeholder',
				'autocomplete',
			]) .
			' />';
		$html .=
			'<input type="hidden" name="' .
			$this->name .
			'" id="' .
			$id .
			'" value="' .
			$this->entity->getId() .
			'" />';

		return $html;
	}

	/**
	 * Dit veld is gepost als show en de pk is gepost.
	 *
	 * @return bool Of alles gepost is
	 */
	public function isPosted(): bool
	{
		if (
			null === filter_input(INPUT_POST, $this->name . '_show', FILTER_DEFAULT)
		) {
			return false;
		}

		if (null === filter_input(INPUT_POST, $this->name, FILTER_DEFAULT)) {
			return false;
		}

		return true;
	}
}
