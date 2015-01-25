<?php

/**
 * KeuzeVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Bevat o.a. de <SELECT> uitbreidingen van InputField:
 * 
 * 	- SelectField
 * 		* GeslachtField				m/v
 * 		* JaNeeField				ja/nee
 * 		* VerticaleField			Verticalen
 * 		* KerkField					Denominaties
 * 		* KeuzeRondjeField			Keuzerondje
 * 
 * 	- VinkField						Keuzevakje
 * 	- DatumField					Datums (want data is zo ambigu)
 * 	- TijdField						Tijsstip
 *  - ColorField					Kleurkiezer
 * 
 */
class ColorField extends InputField {

	public $type = 'color';

}

class RequiredColorField extends ColorField {

	public $required = true;

}

/**
 * SelectField
 * HTML select met opties.
 */
class SelectField extends InputField {

	public $options;
	public $groups;
	public $size;
	public $multiple;

	public function __construct($name, $value, $description, array $options, $groups = false, $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->groups = (boolean) $groups;
		$this->size = (int) $size;
		$this->multiple = $multiple;
		if ($this->groups) {
			$this->onchange .= 'preview' . $this->getId() . '();';
			$this->onkeyup .= 'preview' . $this->getId() . '();';
		}
	}

	public function getValue() {
		$value = parent::getValue();
		if ($this->empty_null AND $value == '') {
			return null;
		}
		return $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->groups) {
			// flatten array
			$options = array();
			foreach ($this->options as $group) {
				$options += $group;
			}
		} else {
			$options = $this->options;
		}
		if ($this->multiple) {
			if (array_intersect($this->value, $options) !== $this->value) {
				$this->error = 'Onbekende optie gekozen';
			}
		} else {
			if (!array_key_exists($this->value, $options)) {
				$this->error = 'Onbekende optie gekozen';
			}
		}
		return $this->error === '';
	}

	public function getPreviewDiv() {
		if ($this->groups) {
			return '<div id="selectPreview_' . $this->getId() . '" class="previewDiv"></div>';
		}
		return '';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

var preview{$this->getId()} = function () {
	var selected = $(':selected', '#{$this->getId()}');
	$('#selectPreview_{$this->getId()}').html(selected.parent().attr('label'));
};
preview{$this->getId()}();
JS;
	}

	public function getHtml() {
		$html = '<select name="' . $this->name;
		if ($this->multiple) {
			$html .= '[]" multiple';
		} else {
			$html .= '"';
		}
		if ($this->size > 1) {
			$html .= ' size="' . $this->size . '"';
		}
		$html .= $this->getInputAttribute(array('id', 'origvalue', 'class', 'disabled', 'readonly')) . '>';
		if ($this->groups) {
			foreach ($this->options as $group => $options) {
				$html .= '<optgroup label="' . htmlspecialchars($group) . '">'
						. $this->getOptionsHtml($options) .
						'</optgroup>';
			}
		} else {
			$html .= $this->getOptionsHtml($this->options);
		}
		return $html . '</select>';
	}

	protected function getOptionsHtml(array $options) {
		$html = '';
		foreach ($options as $value => $description) {
			$html .= '<option value="' . $value . '"';
			if ($value == $this->value) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . htmlspecialchars($description) . '</option>';
		}
		return $html;
	}

}

class RequiredSelectField extends SelectField {

	public $required = true;

}

class MultiSelectField extends InputField {

	private $select = array();

	public function __construct($name, $value, $description, $keuzeopties) {
		parent::__construct($name, $value, $description);
		$gekozen = explode('&&', $value);
		$array = explode('&&', str_replace('&amp;&amp;', '&&', $keuzeopties));
		foreach ($array as $keuze => $opties) {
			$this->select[] = new SelectField($name . '[]', $gekozen[$keuze], null, explode('|', $opties));
		}
	}

	public function isPosted() {
		return isset($_POST[$this->name . '[]']);
	}

	public function getValue() {
		if ($this->isPosted()) {
			$values = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			return implode('&&', $values);
		}
		return parent::getValue();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		foreach ($this->select as $select) {
			$select->validate();
			$this->error .= $select->getError();
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '';
		foreach ($this->select as $select) {
			$html .= $select->getHtml();
		}
		return $html;
	}

}

/**
 * Select an entity based on the primary key while showing the label attributes
 */
class EntityDropDown extends SelectField {

	public function __construct($name, array $primary_key_values, $description, array $options, array $label_attributes, $size = 1, $multiple = false) {
		parent::__construct($name, json_encode($primary_key_values), $description, array(), $size, $multiple);
		if (!$this->required) {
			$this->options[''] = '';
		}
		foreach ($options as $option) {
			$label = array();
			foreach ($label_attributes as $attr) {
				$label[] = $option->$attr;
			}
			$this->options[json_encode($option->getValues(true))] = implode(' ', $label);
		}
	}

	public function getValue() {
		$value = json_decode(parent::getValue());
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return $value;
	}

}

class RequiredEntityDropDown extends EntityDropDown {

	public $required = true;

}

/**
 * Man of vrouw
 */
class GeslachtField extends SelectField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, array('m' => 'Man', 'v' => 'Vrouw'));
	}

}

/**
 * Ja of Nee
 */
class JaNeeField extends SelectField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, array('ja' => 'Ja', 'nee' => 'Nee'));
	}

}

/**
 * Dag van de week
 */
class WeekdagField extends SelectField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, array('0' => 'zondag', '1' => 'maandag', '2' => 'dinsdag', '3' => 'woensdag', '4' => 'donderdag', '5' => 'vrijdag', '6' => 'zaterdag'));
	}

	public function getValue() {
		return (int) parent::getValue();
	}

}

/**
 * Selecteer een verticale. Geeft een volgnummer terug.
 */
class VerticaleField extends SelectField {

	public function __construct($name, $value, $description) {
		$verticalen = array();
		foreach (VerticalenModel::instance()->prefetch() as $v) {
			$verticalen[$v->letter] = $v->naam;
		}
		parent::__construct($name, $value, $description, $verticalen);
	}

}

class KerkField extends SelectField {

	public function __construct($name, $value, $description) {
		$kerken = array(
			'PKN', 'PKN Hervormd', 'PKN Gereformeerd', 'PKN Gereformeerde Bond', 'Hersteld Hervormd',
			'Evangelisch', 'Volle Evangelie Gemeente', 'Gereformeerd Vrijgemaakt', 'Nederlands Gereformeerd',
			'Christelijk Gereformeerd', 'Gereformeerde Gemeenten', 'Pinkstergemeente', 'Katholiek Apostolisch',
			'Vergadering van gelovigen', 'Rooms-Katholiek', 'Baptist');
		parent::__construct($name, $value, $description, $kerken);
	}

}

/**
 * KeuzeRondjeField
 * Zelfde soort mogelijkheden als een SelectField, maar dan minder klikken
 *
 * is valid als één van de opties geselecteerd is
 */
class KeuzeRondjeField extends SelectField {

	public $type = 'radio';

	public function __construct($name, $value, $description, array $options) {
		parent::__construct($name, $value, $description, $options, array(), 1, false);
	}

	public function getHtml() {
		$html = '<div class="KeuzeRondjeOptions">';
		foreach ($this->options as $value => $description) {
			$html .= $this->getOptionHtml($value, $description);
		}
		return $html . '</div>';
	}

	protected function getOptionHtml($value, $description) {
		$html = '<input id="' . $this->getId() . 'Option_' . $value . '" value="' . $value . '" ' . $this->getInputAttribute(array('type', 'name', 'class', 'disabled', 'readonly'));
		if ($value === $this->value) {
			$html .= ' checked="checked"';
		}
		return $html . '><label for="' . $this->getId() . 'Option_' . $value . '" class="KeuzeRondjeLabel"> ' . htmlspecialchars($description) . '</label>';
	}

}

class RequiredKeuzeRondjeField extends KeuzeRondjeField {

	public $required = true;

}

/**
 * DatumField
 *
 * Selecteer een datum, met een mogelijk maximum jaar.
 *
 * Produceert drie velden.
 */
class DatumField extends InputField {

	protected $max_jaar;
	protected $min_jaar;

	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		parent::__construct($name, $value, $description);
		if (is_int($maxyear)) {
			$this->max_jaar = $maxyear;
		} else {
			$this->max_jaar = (int) date('Y') + 10;
		}
		if (is_int($minyear)) {
			$this->min_jaar = $minyear;
		} else {
			$this->min_jaar = (int) date('Y') - 10;
		}
		$jaar = (int) date('Y', strtotime($value));
		if ($jaar > $this->max_jaar) {
			$this->max_jaar = $jaar;
		}
		if ($jaar < $this->min_jaar) {
			$this->min_jaar = $jaar;
		}
	}

	public function isPosted() {
		return isset($_POST[$this->name . '_jaar'], $_POST[$this->name . '_maand'], $_POST[$this->name . '_dag']);
	}

	public function getJaar() {
		return $_POST[$this->name . '_jaar'];
	}

	public function getMaand() {
		return $_POST[$this->name . '_maand'];
	}

	public function getDag() {
		return $_POST[$this->name . '_dag'];
	}

	public function getValue() {
		if ($this->isPosted()) {
			$value = $this->getJaar() . '-' . $this->getMaand() . '-' . $this->getDag();
		} else {
			$value = parent::getValue();
		}
		if ($this->empty_null AND $value == '0000-00-00') {
			return null;
		}
		return $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$jaar = (int) $this->getJaar();
		$maand = (int) $this->getMaand();
		$dag = (int) $this->getDag();
		if ($this->value == '0000-00-00' OR empty($this->value)) {
			if ($this->required) {
				$this->error = 'Dit is een verplicht veld';
			}
		} elseif (!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $this->value) OR ! checkdate($maand, $dag, $jaar)) {
			$this->error = 'Ongeldige datum';
		} elseif (is_int($this->max_jaar) AND $jaar > $this->max_jaar) {
			$this->error = 'Kies een jaar voor ' . $this->max_jaar;
		} elseif (is_int($this->min_jaar) AND $jaar < $this->min_jaar) {
			$this->error = 'Kies een jaar na ' . $this->min_jaar;
		}
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="datumPreview_' . $this->getId() . '" class="previewDiv"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

var preview{$this->getId()} = function () {
	var datum = new Date($('#{$this->getId()}_jaar').val(), $('#{$this->getId()}_maand').val() - 1, $('#{$this->getId()}_dag').val());
	var weekday = [ 'zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag' ];
	$('#datumPreview_{$this->getId()}').html(weekday[datum.getDay()]);
}
preview{$this->getId()}();
$('#{$this->getId()}_dag').change(preview{$this->getId()});
$('#{$this->getId()}_dag').keyup(preview{$this->getId()});
$('#{$this->getId()}_maand').change(preview{$this->getId()});
$('#{$this->getId()}_maand').keyup(preview{$this->getId()});
$('#{$this->getId()}_jaar').change(preview{$this->getId()});
$('#{$this->getId()}_jaar').keyup(preview{$this->getId()});
JS;
	}

	public function getHtml() {
		$years = range($this->min_jaar, $this->max_jaar);
		$months = range(1, 12);
		$days = range(1, 31);

		if (!$this->required) {
			array_unshift($years, 0);
			array_unshift($months, 0);
			array_unshift($days, 0);
		}

		$html = '<select id="' . $this->getId() . '_dag" name="' . $this->name . '_dag" origvalue="' . substr($this->origvalue, 8, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($days as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 8, 2)) {
				$html .= ' selected="selected"';
			}
			$label = (int) $value;
			if ($label > 0) {
				if ($label < 10) {
					$label = '&nbsp;&nbsp;' . $label;
				}
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		$html .= '</select> ';

		$html .= '<select id="' . $this->getId() . '_maand" name="' . $this->name . '_maand" origvalue="' . substr($this->origvalue, 5, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($months as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 5, 2)) {
				$html .= ' selected="selected"';
			}
			$label = (int) $value;
			if ($label > 0) {
				$label = '&nbsp;&nbsp;' . strftime('%B', mktime(0, 0, 0, $label, 1, 0));
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		$html .= '</select> ';

		$html .= '<select id="' . $this->getId() . '_jaar" name="' . $this->name . '_jaar" origvalue="' . substr($this->origvalue, 0, 4) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($years as $value) {
			$value = sprintf('%04d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 4)) {
				$html .= ' selected="selected"';
			}
			if ((int) $value > 0) {
				$label = $value;
			} else {
				$label = '';
			}
			$html .= '>' . $label . '</option>';
		}
		return $html . '</select>';
	}

}

class RequiredDatumField extends DatumField {

	public $required = true;

}

class TijdField extends InputField {

	protected $minutensteps;

	public function __construct($name, $value, $description, $minutensteps = null) {
		parent::__construct($name, $value, $description);
		if ($minutensteps === null) {
			$this->minutensteps = 1;
		} else {
			$this->minutensteps = (int) $minutensteps;
		}
	}

	public function isPosted() {
		return isset($_POST[$this->name . '_uur'], $_POST[$this->name . '_minuut']);
	}

	public function getUur() {
		return $_POST[$this->name . '_uur'];
	}

	public function getMinuut() {
		return $_POST[$this->name . '_minuut'];
	}

	public function getValue() {
		if ($this->isPosted()) {
			return $this->getUur() . ':' . $this->getMinuut();
		} else {
			return parent::getValue();
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$uren = (int) substr($this->value, 0, 2);
		$minuten = (int) substr($this->value, 3, 5);
		if (!preg_match('/^(\d\d?):(\d\d?)$/', $this->value) OR $uren < 0 OR $uren > 23 OR $minuten < 0 OR $minuten > 59) {
			$this->error = 'Ongeldig tijdstip';
		}
		return $this->error === '';
	}

	public function getHtml() {
		$hours = range(0, 23);
		$minutes = range(0, 59, $this->minutensteps);

		$html = '<select id="' . $this->getId() . '_uur" name="' . $this->name . '_uur" origvalue="' . substr($this->origvalue, 0, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($hours as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 2)) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select> ';

		$html .= '<select id="' . $this->getId() . '_minuut" name="' . $this->name . '_minuut" origvalue="' . substr($this->origvalue, 3, 2) . '" ' . $this->getInputAttribute('class') . '>';
		$previousvalue = 0;
		foreach ($minutes as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value > $previousvalue && $value <= substr($this->value, 3, 2)) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $value . '</option>';
			$previousvalue = $value;
		}
		return $html . '</select>';
	}

}

class RequiredTijdField extends TijdField {

	public $required = true;

}

class VinkField extends InputField {

	public $type = 'checkbox';
	public $label;

	public function __construct($name, $value, $description, $label = null, $model = null) {
		parent::__construct($name, $value, $description, $model);
		$this->label = $label;
	}

	/**
	 * Speciaal geval:
	 * Niets gepost = niet gepost.
	 * 
	 * @return boolean
	 */
	public function isPosted() {
		return !empty($_POST);
	}

	/**
	 * Speciaal geval:
	 * Uitgevinkt = niet gepost.
	 * 
	 * @return boolean
	 */
	public function getValue() {
		if (parent::isPosted()) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if (!$this->value AND $this->required) {
			if ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
				// exception for leden mod
			} else {
				$this->error = 'Dit is een verplicht veld';
			}
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'value', 'origvalue', 'class', 'disabled', 'readonly'));
		if ($this->value) {
			$html .= ' checked="checked" ';
		}
		$html .= '/>';

		if (!empty($this->label)) {
			$html .= '<label for="' . $this->getId() . '" class="VinkFieldLabel">' . $this->label . '</label>';
		}
		return $html;
	}

}

class RequiredVinkField extends VinkField {

	public $required = true;

}
