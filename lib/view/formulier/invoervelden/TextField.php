<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\Validator;

/**
 * Simpelste variant van een <input> element, zonder fratsen.
 *
 * @package CsrDelft\view\formulier\invoervelden
 */
class TextField implements FormElement, Validator
{
	public $cssClasses = ['FormElement', 'form-control'];
	public $title;
	public $required = false;
	public $readonly = false;
	public $placeholder;
	public $max_len;
	public $step;
	public $pattern;
	public $min;
	public $max;
	public $min_len;
	public $autocomplete = true;
	protected $name;
	protected $origvalue;
	protected $type = 'text';
	protected $error = '';
	protected $value;
	protected $wrapperClassName = 'row mb-3';
	protected $labelClassName = 'col-3 col-form-label';
	protected $fieldClassName = 'col-9';
	protected $description;
	private $id;
	private $model;

	public function __construct($name, $value, $description, $max_len = 255, $min_len = 0, $model = null)
	{
		$this->id = uniqid_safe('field_');
		$this->model = $model;
		$this->name = $name;
		$this->origvalue = $value;
		if ($this->isPosted()) {
			$this->value = $this->getValue();
		} else {
			$this->value = $value;
		}
		$this->description = $description;
		// add *Field classname to css_classes
		$this->cssClasses[] = classNameZonderNamespace(get_class($this));

		if ($description === null) {
			$this->labelClassName .= ' d-none';
			$this->fieldClassName = str_replace('col-9', 'col', $this->fieldClassName);
		}
		$this->max_len = $max_len;
		$this->min_len = $min_len;
	}

	public function isPosted()
	{
		return isset($_POST[$this->name]);
	}

	public function getValue()
	{
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name, FILTER_UNSAFE_RAW);
		}
		return $this->value;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	public function getOrigValue()
	{
		return $this->origvalue;
	}

	/**
	 * Value returned from this field
	 */
	public function getFormattedValue()
	{
		return $this->getValue();
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getType()
	{
		return $this->type;
	}

	/**
	 * View die zou moeten werken voor veel velden.
	 */
	public function __toString()
	{
		$html = '';
		$html .= $this->getDiv();
		$html .= $this->getLabel();
		$html .= '<div class="' . $this->fieldClassName . '">';
		$html .= $this->getHtml();
		$html .= $this->getErrorDiv();
		$html .= '</div>';
		$html .= $this->getHelpDiv();
		$html .= '</div>';
		return $html;
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	public function getDiv()
	{
		$cssclass = $this->wrapperClassName;
		return '<div id="wrapper_' . $this->id . '" class="' . $cssclass . '">';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	public function getLabel()
	{
		if (!empty($this->description)) {
			$required = '';
			if ($this->required) {
				if (!LoginService::mag(P_LEDEN_MOD)) {
					$required = '<span class="field-required">*</span>';
				}
			}
			return '<div class="' . $this->labelClassName . '"><label for="' . $this->getId() . '">' . $this->description . $required . '</label></div>';
		}
		return '';
	}

	public function getId()
	{
		return $this->id;
	}

	public function getHtml()
	{
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ' />';
	}

	/**
	 * Gecentraliseerde genereermethode voor de attributen van de
	 * input-tag.
	 * Dit is bij veel dingen het zelfde, en het is niet zo handig om in
	 * elke instantie dan bijvoorbeeld de prefix van het id-veld te
	 * moeten aanpassen. Niet meer nodig dus.
	 */
	protected function getInputAttribute($attribute)
	{
		if (is_array($attribute)) {
			$return = '';
			foreach ($attribute as $a) {
				$return .= ' ' . $this->getInputAttribute($a);
			}
			return $return;
		}
		switch ($attribute) {
			case 'id':
				return 'id="' . $this->getId() . '"';
			case 'class':
				return 'class="' . implode(' ', $this->getCssClasses()) . '"';
			case 'value':
				return 'value="' . htmlspecialchars($this->value) . '"';
			case 'origvalue':
				return 'origvalue="' . htmlspecialchars($this->origvalue) . '"';
			case 'name':
				return 'name="' . $this->name . '"';
			case 'type':
				return 'type="' . $this->type . '"';
			case 'readonly':
				if ($this->readonly) {
					return 'readonly';
				}
				break;
			case 'placeholder':
				if ($this->placeholder != null) {
					return 'placeholder="' . $this->placeholder . '"';
				}
				break;
			case 'maxlength':
				if (is_int($this->max_len)) {
					return 'maxlength="' . $this->max_len . '"';
				}
				break;

			case 'autocomplete':
				if (!$this->autocomplete) {
					return 'autocomplete="off"'; // browser autocompete
				}
				break;

			case 'pattern':
				if ($this->pattern) {
					return 'pattern="' . $this->pattern . '"';
				}
				break;
			case 'step':
				if ($this->step > 0) {
					return 'step="' . $this->step . '"';
				}
				break;
			case 'min':
				if ($this->min !== null) {
					return 'min="' . $this->min . '"';
				}
				break;
			case 'max':
				if ($this->max !== null) {
					return 'max="' . $this->max . '"';
				}
				break;
		}
		return '';
	}

	/**
	 * Geef lijst van allerlei CSS-classes voor dit veld terug.
	 */
	protected function getCssClasses()
	{
		$classes = $this->cssClasses;
		if ($this->required && !LoginService::mag(P_LEDEN_MOD)) {
			$classes[] = 'required';
		}
		if ($this->readonly) {
			$classes[] = 'readonly';
		}

		if ($this->getError() != '') {
			$classes[] = 'is-invalid';
		}

		return $classes;
	}

	/**
	 * Geef de foutmelding voor dit veld terug.
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Geef een div met de foutmelding voor dit veld terug.
	 */
	public function getErrorDiv()
	{
		if ($this->getError() != '') {
			return '<div class="display-block invalid-feedback">' . $this->getError() . '</div>';
		}
		return '';
	}

	public function getHelpDiv()
	{
		if ($this->title) {
			return '<div class="form-text">' . $this->title . '</div>';
		}
		return '';
	}

	public function getJavascript()
	{
		return '';
	}

	/**
	 * Is de invoer voor het veld correct?
	 * standaard krijgt deze functie de huidige waarde mee als argument
	 *
	 * Kindertjes van deze classe kunnen deze methode overloaden om specifiekere
	 * testen mogelijk te maken.
	 */
	public function validate()
	{
		if (!$this->isPosted()) {
			$this->error = 'Veld is niet gepost';
		} elseif ($this->readonly && $this->value != $this->origvalue) {
			$this->error = 'Dit veld mag niet worden aangepast';
		} elseif ($this->value == '' && $this->required) {
			// vallen over lege velden als dat aangezet is voor het veld
			if (!LoginService::mag(P_LEDEN_MOD)) {
				$this->error = 'Dit is een verplicht veld';
			}
		}
		// als max_len is gezet dan checken of de lengte er niet boven zit
		if (is_int($this->max_len) && strlen($this->value) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		// als min_len is gezet dan checken of de lengte er niet onder zit
		if (is_int($this->min_len) && strlen($this->value) < $this->min_len) {
			$this->error = 'Dit veld moet minimaal ' . $this->min_len . ' tekens lang zijn';
		}

		return $this->error === '';
	}

	public function getTitel()
	{
		return $this->description;
	}

	public function getBreadcrumbs()
	{
		return '';
	}
}
