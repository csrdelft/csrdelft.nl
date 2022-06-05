<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\ISelectEntity;
use CsrDelft\view\formulier\invoervelden\InputField;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class EntitySelectField extends InputField
{
	public $size;
	/**
	 * @var ISelectEntity[]
	 */
	protected $options;
	private $entityType;
	/**
	 * @var ObjectRepository
	 */
	private $repository;
	/**
	 * @var ObjectManager
	 */
	private $entityManager;

	public function __construct($name, $value, $description, $entityType)
	{
		$this->css_classes = ['FormElement', 'form-select'];

		if (!in_array(ISelectEntity::class, class_implements($entityType))) {
			throw new CsrException($entityType . ' implementeerd niet ISelectEntity');
		}

		parent::__construct($name, $value ? $value->getId() : null, $description);

		$this->entityType = $entityType;
		$doctrine = ContainerFacade::getContainer()->get('doctrine');
		$this->repository = $doctrine->getRepository($entityType);
		$this->entityManager = ContainerFacade::getContainer()->get(
			'doctrine.orm.entity_manager'
		);

		$this->options = $this->repository->findAll();
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}

		if (
			($this->required || $this->getValue() !== null) &&
			!in_array($this->value, $this->getOptionIds())
		) {
			$this->error = 'Onbekende optie gekozen';
		}
		return $this->error === '';
	}

	public function getFormattedValue()
	{
		$value = $this->getValue();

		if (!$value) {
			return null;
		}

		return $this->entityManager->getReference($this->entityType, $value);
	}

	public function getHtml($include_hidden = true)
	{
		$html = '';
		if ($include_hidden) {
			$html .= '<input type="hidden" name="' . $this->name . '" value="" />';
		}
		$html .= '<select name="' . $this->name;
		$html .= '"';
		if ($this->size > 1) {
			$html .= ' size="' . $this->size . '"';
		}
		$html .=
			$this->getInputAttribute([
				'id',
				'origvalue',
				'class',
				'disabled',
				'readonly',
			]) . '>';
		$html .= $this->getOptionsHtml($this->options);
		return $html . '</select>';
	}

	/**
	 * @param ISelectEntity[] $options
	 * @return string
	 */
	protected function getOptionsHtml(array $options)
	{
		$html = '';
		foreach ($options as $description) {
			$html .= '<option value="' . $description->getId() . '"';
			if ($this->value && $description->getId() == $this->value) {
				$html .= ' selected="selected"';
			}
			$html .=
				'>' .
				str_replace('&amp;', '&', htmlspecialchars($description->getValue())) .
				'</option>';
		}
		if ($this->value == null) {
			$html .= "<option hidden disabled selected value=''></option>";
		}
		return $html;
	}

	/**
	 * @param ISelectEntity[] $options
	 */
	public function setOptions(array $options): void
	{
		$this->options = $options;
	}

	public function getOptionIds()
	{
		return array_map(function ($option) {
			return $option->getId();
		}, $this->options);
	}
}
