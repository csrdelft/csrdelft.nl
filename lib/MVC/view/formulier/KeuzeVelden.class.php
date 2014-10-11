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
 * 	- DatumField					Datums (want data is zo ambigu)
 * 	- TijdField						Tijsstip
 * 
 *  - KeuzeRondjeField				Keuzerondje
 * 	- VinkField						Keuzevakje
 * 
 */

/**
 * SelectField
 * HTML select met opties.
 */
class SelectField extends InputField {

	public $options;
	public $size;
	public $multiple;

	public function __construct($name, $value, $description, array $options, $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->size = (int) $size;
		$this->multiple = $multiple;
	}

	public function getValue() {
		$value = parent::getValue();
		if ($this->empty_null AND empty($value)) {
			return null;
		}
		return $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->multiple) {
			if (array_intersect($this->value, $this->options) !== $this->value) {
				$this->error = 'Onbekende optie gekozen';
			}
		} else {
			if (!array_key_exists($this->value, $this->options)) {
				$this->error = 'Onbekende optie gekozen';
			}
		}
		return $this->error === '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<select name="' . $this->name;
		if ($this->multiple) {
			echo '[]" multiple';
		} else {
			echo '"';
		}
		if ($this->size > 1) {
			echo ' size="' . $this->size . '"';
		}
		echo $this->getInputAttribute(array('id', 'origvalue', 'class', 'disabled', 'readonly', 'onchange', 'onclick', 'onkeyup')) . '>';

		foreach ($this->options as $value => $description) {
			echo '<option value="' . $value . '"';
			if ($value == $this->value) {
				echo ' selected="selected"';
			}
			echo '>' . htmlspecialchars($description) . '</option>';
		}
		echo '</select>';

		echo '</div>';
	}

}

class RequiredSelectField extends SelectField {

	public $required = true;

}

/**
 * Select an entity based on the primary key while showing the label attributes
 */
class EntityDropDown extends SelectField {

	public function __construct($name, $value, $description, array $options, array $label_attributes, $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description, array(), $size, $multiple);
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
		if ($this->empty_null AND empty($value)) {
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

	public function __construct($name, $value, $description = null) {
		parent::__construct($name, $value, $description, array('m' => 'Man', 'v' => 'Vrouw'));
	}

}

/**
 * Ja of Nee
 */
class JaNeeField extends SelectField {

	public function __construct($name, $value, $description = null) {
		parent::__construct($name, $value, $description, array('ja' => 'Ja', 'nee' => 'Nee'));
	}

}

/**
 * Dag van de week
 */
class WeekdagField extends SelectField {

	public function __construct($name, $value, $description = null) {
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

	public function __construct($name, $value, $description = null) {
		$verticalen = OldVerticale::getNamen();
		parent::__construct($name, $value, $description, $verticalen);
	}

}

class KerkField extends SelectField {

	public function __construct($name, $value, $description = null) {
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

	public function __construct($name, $value, $description, array $options) {
		parent::__construct($name, $value, $description, $options, array(), 1, false);
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<div class="KeuzeRondjeFieldOptions">';
		foreach ($this->options as $value => $description) {
			echo '<input type="radio" id="field_' . $this->getName() . '_option_' . $value . '" value="' . $value . '"' . $this->getInputAttribute(array('name', 'origvalue', 'class', 'disabled', 'readonly', 'onchange', 'onclick', 'onkeyup'));
			if ($value == $this->value) {
				echo ' checked="checked"';
			}
			echo '><label for="field_' . $this->getName() . '_option_' . $value . '" ' . $this->getInputAttribute('class') . '> ' . htmlspecialchars($description) . '</label><br />';
		}
		echo '</div>';

		echo '</div>';
	}

}

/**
 * DatumField
 *
 * Selecteer een datum, met een mogelijk maximum jaar.
 *
 * Produceert drie velden.
 */
class DatumField extends InputField {

	protected $maxyear;
	protected $minyear;

	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		parent::__construct($name, $value, $description);
		if ($maxyear === null) {
			$this->maxyear = date('Y');
		} else {
			$this->maxyear = (int) $maxyear;
		}
		if ($minyear === null) {
			$this->minyear = 1920;
		} else {
			$this->minyear = (int) $minyear;
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
		if (!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $this->value)) {
			$this->error = 'Ongeldige datum';
		} elseif (substr($this->value, 0, 4) > $this->maxyear) {
			$this->error = 'Er kunnen geen data later dan ' . $this->maxyear . ' worden weergegeven';
		} elseif ($this->value != '0000-00-00' AND ! checkdate($this->getMaand(), $this->getDag(), $this->getJaar())) {
			$this->error = 'Datum bestaat niet';
		}
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="datumPreview_' . $this->getId() . '" class="datumPreview"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
onChange_{$this->getId()} = function (){
	var datum = new Date($('#field_{$this->name}_jaar').val(), $('#field_{$this->name}_maand').val() - 1, $('#field_{$this->name}_dag').val());
	var weekday = [ 'zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag' ];
	$('#datumPreview_{$this->getId()}').html(weekday[datum.getDay()]);
}
onChange_{$this->getId()}();
JS;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		$onchange = ' onchange="onChange_' . $this->getId() . '()" onkeyup="onChange_' . $this->getId() . '()"';
		$years = range($this->minyear, $this->maxyear);
		$months = range(1, 12);
		$days = range(1, 31);

		//als de datum al nul is, moet ie dat ook weer kunnen worden...
		if ($this->value == '0000-00-00' OR $this->value == 0) {
			$years[] = '0000';
			$months[] = 0;
			$days[] = 0;
		}

		echo '<select id="field_' . $this->name . '_dag" name="' . $this->name . '_dag" origvalue="' . substr($this->origvalue, 8, 2) . '" ' . $this->getInputAttribute('class') . $onchange . '>';
		foreach ($days as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 8, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_maand" name="' . $this->name . '_maand" origvalue="' . substr($this->origvalue, 5, 2) . '" ' . $this->getInputAttribute('class') . $onchange . '>';
		foreach ($months as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 5, 2)) {
				echo ' selected="selected"';
			}

			echo '>' . strftime('%B', mktime(0, 0, 0, $value, 1, 0)) . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_jaar" name="' . $this->name . '_jaar" origvalue="' . substr($this->origvalue, 0, 4) . '" ' . $this->getInputAttribute('class') . $onchange . '>';
		foreach ($years as $value) {
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 4)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select>';

		echo $this->getPreviewDiv();
		echo '</div>';
	}

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
		if (!preg_match('/^(\d\d?):(\d{2})$/', $this->value)) {
			$this->error = 'Ongeldige tijdstip';
		} elseif (substr($this->value, 0, 2) > 23 OR substr($this->value, 3, 5) > 59) {
			$this->error = 'Tijdstip bestaat niet';
		}
		return $this->error === '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		$hours = range(0, 23);
		$minutes = range(0, 59, $this->minutensteps);

		echo '<select id="field_' . $this->name . '_uur" name="' . $this->name . '_uur" origvalue="' . substr($this->origvalue, 0, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($hours as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_minuut" name="' . $this->name . '_minuut" origvalue="' . substr($this->origvalue, 3, 2) . '" ' . $this->getInputAttribute('class') . '>';
		$previousvalue = 0;
		foreach ($minutes as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value > $previousvalue && $value <= substr($this->value, 3, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
			$previousvalue = $value;
		}
		echo '</select>';
		echo '</div>';
	}

}

class VinkField extends InputField {

	public $label;

	public function __construct($name, $value, $description = null, $label = null, $model = null) {
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

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<input type="checkbox"' . $this->getInputAttribute(array('id', 'name', 'value', 'origvalue', 'class', 'disabled', 'readonly', 'onchange', 'onclick', 'onkeyup'));
		if ($this->value) {
			echo ' checked="checked" ';
		}
		echo '/>';

		if (!empty($this->label)) {
			echo '<label for="field_' . $this->name . '" class="VinkFieldLabel">' . $this->label . '</label>';
		}

		echo '</div>';
	}

}

class RequiredVinkField extends VinkField {

	public $required = true;

}
