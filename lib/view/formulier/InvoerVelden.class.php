<?php

/**
 * InvoerVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Bevat de uitbreidingen van InputField:
 * 
 * 	- TextField						Simpele input
 * 		* DateTimeField				Datum & tijdstip
 * 		* RechtenField				Rechten, zie AccessModel
 * 		* LandField					Landen
 * 		* StudieField				Opleidingen
 * 		* EmailField				Email adressen
 * 		* UrlField					Url's
 * 		* TextareaField				Textarea die automagisch uitbreidt bij typen
 * 			- BBCodeField		Textarea met bbcode voorbeeld
 *  	* NickField					Nicknames
 *  	* DuckField					Ducknames
 * 		* LidField					Leden selecteren
 * 	- WachtwoordWijzigenField		Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 *  - EntityField					PersistentEntity primary key values array
 * 
 * 
 * Meer uitbreidingen van InputField:
 * @see GetalVelden.class.php
 * @see KeuzeVelden.class.php
 * 
 */

/**
 * InputField is de base class van alle FormElements die data leveren,
 * behalve FileField zelf die wel meerdere InputFields bevat.
 */
abstract class InputField implements FormElement, Validator {

	private $id; // unique id
	protected $model; // model voor remote data source en validatie
	protected $name; // naam van het veld in POST
	protected $value; // welke initiele waarde heeft het veld?
	protected $origvalue; // welke originele waarde had het veld?
	protected $empty_null = true; // lege waarden teruggeven als null (SET BEFORE getValue() call in constructor!)
	public $type = 'text'; // input type
	public $title; // omschrijving bij mouseover title
	public $description; // omschrijving in label
	public $hidden = false; // veld onzichtbaar voor gebruiker?
	public $readonly = false; // veld mag niet worden aangepast door client?
	public $required = false; // mag het veld leeg zijn?
	public $enter_submit = false; // bij op enter drukken form submitten
	public $escape_cancel = false; // bij op escape drukken form annuleren
	public $preview = true; // preview tonen? (waar van toepassing)
	public $leden_mod = false; // uitzondering leeg verplicht veld voor LEDEN_MOD
	public $autocomplete = true; // browser laten autoaanvullen?
	public $placeholder = null; // plaats een grijze placeholdertekst in leeg veld
	public $error = ''; // foutmelding van dit veld
	public $onchange = null; // callback on change of value
	public $onchange_submit = false; // bij change of value form submitten
	public $onclick = null; // callback on click
	public $onkeydown = null; // prevent illegal character from being entered
	public $onkeyup = null; // respond to keyboard strokes
	public $typeahead_selected = null; // callback gekozen suggestie
	public $max_len = null; // maximale lengte van de invoer
	public $min_len = null; // minimale lengte van de invoer
	public $rows = 0; // aantal rijen van textarea
	public $css_classes = array('FormElement'); // array met classnames die later in de class-tag komen
	public $suggestions = array(); // lijst van search providers
	public $blacklist = null; // array met niet tegestane waarden
	public $whitelist = null; // array met exclusief toegestane waarden
	public $pattern = null; // html5 input validation pattern

	public function __construct($name, $value, $description, $model = null) {
		$this->id = uniqid('field_');
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
		$this->css_classes[] = get_class($this);
	}

	public function getType() {
		return $this->type;
	}

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->description;
	}

	public function getName() {
		return $this->name;
	}

	public function getId() {
		return $this->id;
	}

	public function isPosted() {
		return isset($_POST[$this->name]);
	}

	public function getOrigValue() {
		return $this->origvalue;
	}

	public function getValue() {
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name, FILTER_UNSAFE_RAW);
		}
		return $this->value;
	}

	/**
	 * Is de invoer voor het veld correct?
	 * standaard krijgt deze functie de huidige waarde mee als argument
	 * 
	 * Kindertjes van deze classe kunnen deze methode overloaden om specifiekere
	 * testen mogelijk te maken.
	 */
	public function validate() {
		if (!$this->isPosted()) {
			$this->error = 'Veld is niet gepost';
		} elseif ($this->readonly AND $this->value !== $this->origvalue) {
			$this->error = 'Dit veld mag niet worden aangepast';
		} elseif ($this->value == '' AND $this->required) {
			// vallen over lege velden als dat aangezet is voor het veld
			if ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
				// tenzij gebruiker P_LEDEN_MOD heeft en deze optie aan staat voor dit veld
			} else {
				$this->error = 'Dit is een verplicht veld';
			}
		}
		// als max_len is gezet dan checken of de lengte er niet boven zit
		if (is_int($this->max_len) AND strlen($this->value) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		// als min_len is gezet dan checken of de lengte er niet onder zit
		if (is_int($this->min_len) AND strlen($this->value) < $this->min_len) {
			$this->error = 'Dit veld moet minimaal ' . $this->min_len . ' tekens lang zijn';
		}
		// als blacklist is gezet dan controleren
		if (is_array($this->blacklist) AND in_array_i($this->value, $this->blacklist)) {
			$this->error = 'Deze waarde is niet toegestaan: ' . htmlspecialchars($this->value);
		}
		// als whitelist is gezet dan controleren
		if (is_array($this->whitelist) AND ! in_array_i($this->value, $this->whitelist)) {
			$this->error = 'Deze waarde is niet toegestaan: ' . htmlspecialchars($this->value);
		}
		return $this->error === '';
	}

	/**
	 * Bestand opslaan op de juiste plek.
	 * 
	 * @param string $directory fully qualified path with trailing slash
	 * @param string $filename filename with extension
	 * @param boolean $overwrite allowed to overwrite existing file
	 * @throws Exception Ongeldige bestandsnaam, doelmap niet schrijfbaar of naam ingebruik
	 */
	protected function opslaan($directory, $filename, $overwrite = false) {
		if (!$this->isAvailable()) {
			throw new Exception('Uploadmethode niet beschikbaar: ' . get_class($this));
		}
		if (!$this->validate()) {
			throw new Exception($this->getError());
		}
		if (!valid_filename($filename)) {
			throw new Exception('Ongeldige bestandsnaam: ' . htmlspecialchars($filename));
		}
		if (!file_exists($directory)) {
			mkdir($directory);
		}
		if (false === @chmod($directory, 0755)) {
			throw new Exception('Geen eigenaar van map: ' . htmlspecialchars($directory));
		}
		if (!is_writable($directory)) {
			throw new Exception('Doelmap is niet beschrijfbaar: ' . htmlspecialchars($directory));
		}
		if (file_exists($directory . $filename)) {
			if ($overwrite) {
				if (!unlink($directory . $filename)) {
					throw new Exception('Overschrijven mislukt: ' . htmlspecialchars($directory . $filename));
				}
			} elseif (!$this instanceof BestandBehouden) {
				throw new Exception('Bestandsnaam al in gebruik: ' . htmlspecialchars($directory . $filename));
			}
		}
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	public function getDiv() {
		$cssclass = 'InputField';
		if ($this->hidden) {
			$cssclass .= ' verborgen';
		}
		if ($this->title) {
			$cssclass .= ' hoverIntent';
		}
		if ($this->getError() !== '') {
			$cssclass .= ' metFouten';
		}
		return '<div id="wrapper_' . $this->getId() . '" class="' . $cssclass . '" ' . $this->getInputAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	public function getLabel() {
		if (!empty($this->description)) {
			$required = '';
			if ($this->required) {
				if ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
					// exception for leden mod
				} else {
					$required = '<span class="required"> *</span>';
				}
			}
			$help = '';
			if ($this->title) {
				$help = '<div class="help" onclick="alert(\'' . addslashes($this->title) . '\');"><img width="16" height="16" class="icon hoverIntentContent" alt="?" src="/plaetjes/famfamfam/help.png"></div>';
			}
			return '<label for="' . $this->getId() . '">' . $help . $this->description . $required . '</label>';
		}
		return '';
	}

	/**
	 * Geef de foutmelding voor dit veld terug.
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Geef een div met de foutmelding voor dit veld terug.
	 */
	public function getErrorDiv() {
		if ($this->getError() != '') {
			return '<div class="waarschuwing">' . $this->getError() . '</div>';
		}
		return '';
	}

	public function getPreviewDiv() {
		return '';
	}

	/**
	 * Geef lijst van allerlei CSS-classes voor dit veld terug.
	 */
	protected function getCssClasses() {
		if ($this->required) {
			if ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
				// exception for leden mod
			} else {
				$this->css_classes[] = 'required';
			}
		}
		if ($this->readonly) {
			$this->css_classes[] = 'readonly';
		}
		return $this->css_classes;
	}

	/**
	 * Gecentraliseerde genereermethode voor de attributen van de
	 * input-tag.
	 * Dit is bij veel dingen het zelfde, en het is niet zo handig om in
	 * elke instantie dan bijvoorbeeld de prefix van het id-veld te
	 * moeten aanpassen. Niet meer nodig dus.
	 */
	protected function getInputAttribute($attr) {
		if (is_array($attr)) {
			$return = '';
			foreach ($attr as $a) {
				$return .= ' ' . $this->getInputAttribute($a);
			}
			return $return;
		}
		switch ($attr) {
			case 'id': return 'id="' . $this->getId() . '"';
			case 'class': return 'class="' . implode(' ', $this->getCssClasses()) . '"';
			case 'value': return 'value="' . htmlspecialchars($this->value) . '"';
			case 'origvalue': return 'origvalue="' . htmlspecialchars($this->origvalue) . '"';
			case 'name': return 'name="' . $this->name . '"';
			case 'type':
				if ($this->hidden) {
					$type = 'hidden';
				} else {
					$type = $this->type;
				}
				return 'type="' . $type . '"';
			case 'title':
				if ($this->title) {
					return 'title="' . htmlspecialchars($this->title) . '"';
				}
				break;
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
			case 'rows':
				if (is_int($this->rows)) {
					return 'rows="' . $this->rows . '"';
				}
				break;

			case 'autocomplete':
				if (!$this->autocomplete OR ! empty($this->suggestions)) {
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

	public function getHtml() {
		return '<input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ' />';
	}

	/**
	 * View die zou moeten werken voor veel velden.
	 */
	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		echo $this->getHtml();
		if ($this->preview) {
			echo $this->getPreviewDiv();
		}
		echo '</div>';
	}

	/**
	 * Javascript nodig voor dit *Field. Dit wordt één keer per *Field
	 * geprint door het Formulier-object.
	 * 
	 * TODO: client side validation
	 * 
	 * Toelichting op options voor RemoteSuggestions:
	 * result = array(
	 * 		array(data:array(..,..,..), value: "string", result:"string"),
	 * 		array(... )
	 * )
	 * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
	 */
	public function getJavascript() {
		$js = <<<JS

/* {$this->name} */
JS;
		if ($this->readonly) {
			return $js;
		}
		if ($this->onchange_submit) {
			$this->onchange .= <<<JS

	form_submit(event);
JS;
		}
		if ($this->enter_submit) {
			$this->onkeydown .= <<<JS

	if (event.keyCode === 13) {
		event.preventDefault();
	}
JS;
			$this->onkeyup .= <<<JS

	if (event.keyCode === 13) {
		form_submit(event);
	}
JS;
		}
		if ($this->escape_cancel) {
			$this->onkeydown .= <<<JS

	if (event.keyCode === 27) {
		form_cancel(event);
	}
JS;
		}
		if ($this->onchange !== null) {
			$js .= <<<JS

$('#{$this->getId()}').change(function(event) {
	{$this->onchange}
});
JS;
		}
		if ($this->onclick !== null) {
			$js .= <<<JS

$('#{$this->getId()}').click(function(event) {
	{$this->onclick}
});
JS;
		}
		if ($this->onkeydown !== null) {
			$js .= <<<JS

$('#{$this->getId()}').keydown(function(event) {
	{$this->onkeydown}
});
JS;
		}
		if ($this->onkeyup !== null) {
			$js .= <<<JS

$('#{$this->getId()}').keyup(function(event) {
	{$this->onkeyup}
});
JS;
		}
		$dataset = array();
		foreach ($this->suggestions as $name => $source) {
			$dataset[$name] = uniqid($this->name);

			$js .= <<<JS

var {$dataset[$name]} = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	limit: 5,
JS;
			if (is_array($source)) {
				$suggestions = array_values($source);
				foreach ($suggestions as $i => $suggestion) {
					if (!is_array($suggestion)) {
						$suggestions[$i] = array('value' => $suggestion);
					}
				}
				$json = json_encode($suggestions);
				$js .= <<<JS

	local: {$json}

JS;
			} else {
				$js .= <<<JS

	remote: "{$source}%QUERY"

JS;
			}
			$js .= <<<JS
});
{$dataset[$name]}.initialize();
JS;
		}
		if (!empty($this->suggestions)) {
			$js .= <<<JS

$('#{$this->getId()}').typeahead({
	autoselect: true,
	hint: true,
	highlight: true,
	minLength: 1
}
JS;
		}
		foreach ($this->suggestions as $name => $source) {
			if (is_int($name)) {
				$header = '';
			} else {
				$header = 'header: "<h3>' . $name . '</h3>"';
			}
			$js .= <<<JS
, {
	name: "{$dataset[$name]}",
	displayKey: "value",
	source: {$dataset[$name]}.ttAdapter(),
	templates: {
		{$header}
	}
}
JS;
		}
		if (!empty($this->suggestions)) {
			$js .= <<<JS
);
JS;
			$this->typeahead_selected .= <<<JS

$(this).trigger('change');
JS;
		}
		if ($this->typeahead_selected !== null) {
			$js .= <<<JS

$('#{$this->getId()}').on('typeahead:selected', function (event, suggestion, dataset) {
	{$this->typeahead_selected}
});
JS;
		}
		return $js;
	}

}

/**
 * Een TextField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class TextField extends InputField {

	public function __construct($name, $value, $description, $max_len = 255, $min_len = 0, $model = null) {
		parent::__construct($name, $value === null ? $value : htmlspecialchars_decode($value), $description, $model);
		if (is_int($max_len)) {
			$this->max_len = $max_len;
		}
		if (is_int($min_len)) {
			$this->min_len = $min_len;
		}
		if ($this->isPosted()) {
			// reverse InputField constructor $this->getValue()
			$this->value = htmlspecialchars_decode($this->value);
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value !== null AND ! is_utf8($this->value)) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst';
		}
		return $this->error === '';
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return htmlspecialchars($this->value);
	}

}

class RequiredTextField extends TextField {

	public $required = true;

}

class FileNameField extends TextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value !== '' AND ! valid_filename($this->value)) {
			$this->error = 'Ongeldige bestandsnaam';
		}
		return $this->error === '';
	}

}

class RequiredFileNameField extends FileNameField {

	public $required = true;

}

/**
 * LandField met een aantal autocomplete suggesties voor landen.
 * Doet verder geen controle op niet-bestaande landen...
 */
class LandField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$this->suggestions[] = array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
	}

}

class RequiredLandField extends LandField {

	public $required = true;

}

class RechtenField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$this->suggestions[] = AccessModel::instance()->getPermissionSuggestions();
		$this->suggestions['Verticale'] = '/verticalen/zoeken/?q=';
		$this->suggestions['Lichting'] = '/groepen/lichtingen/zoeken/?q=';
		$this->suggestions['Commissie'] = '/groepen/commissies/zoeken/?q=';
		$this->suggestions['Groep'] = '/groepen/overig/zoeken/?q=';
		$this->suggestions['Ondervereniging'] = '/groepen/onderverenigingen/zoeken/?q=';
		$this->suggestions['Woonoord/Huis'] = '/groepen/woonoorden/zoeken/?q=';
		$this->title = 'Met , en + voor respectievelijk OR en AND. Gebruik | voor OR binnen AND (alsof er haakjes omheen staan)';
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$error = array();
		// OR
		$or = explode(',', $this->value);
		foreach ($or as $and) {
			// AND
			$and = explode('+', $and);
			foreach ($and as $or2) {
				// OR (secondary)
				$or2 = explode('|', $or2);
				foreach ($or2 as $perm) {
					if (!AccessModel::instance()->isValidPerm($perm)) {
						$error[] = 'Ongeldig: "' . $perm . '"';
					}
				}
			}
		}
		$this->error = implode(' & ', $error);
		return $this->error === '';
	}

}

class RequiredRechtenField extends RechtenField {

	public $required = true;

}

class LidField extends TextField {

	// zoekfilter voor door namen2uid gebruikte LidZoeker::zoekLeden. 
	// geaccepteerde input: 'leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'
	private $zoekin;

	public function __construct($name, $value, $description, $zoekin = 'alleleden') {
		parent::__construct($name, $value, $description);
		if (!in_array($zoekin, array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'))) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->suggestions[ucfirst($this->zoekin)] = '/tools/naamsuggesties/' . $this->zoekin . '?q=';
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND empty($this->value)) {
			return null;
		}
		if (!AccountModel::isValidUid($this->value)) {
			$uid = namen2uid($this->value, $this->zoekin);
			if (isset($uid[0]['uid'])) {
				$this->value = $uid[0]['uid'];
			}
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$value = parent::getValue();
		// geldig uid?
		if (AccountModel::isValidUid($value) AND ProfielModel::existsUid($value)) {
			return true;
		}
		$uid = namen2uid($value, $this->zoekin);
		if ($uid) {
			// uniek bestaand lid?
			if (isset($uid[0]['uid']) AND ProfielModel::existsUid($uid[0]['uid'])) {
				return true;
			}
			// meerdere naamopties?
			elseif (count($uid[0]['naamOpties']) > 0) {
				$this->error = 'Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error = 'Geen geldig lid';
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getId() . '" class="previewDiv"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

var preview{$this->getId()} = function() {
	var val = $('#{$this->getId()}').val();
	if (val.length < 1) {
		$('#lidPreview_{$this->getId()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?zoekin={$this->zoekin}&naam=" + val,
	}).done(function(response) {
		$('#lidPreview_{$this->getId()}').html(response);
		init_context('#lidPreview_{$this->getId()}');
	});
};
preview{$this->getId()}();
$('#{$this->getId()}').change(preview{$this->getId()});
JS;
	}

}

class RequiredLidField extends LidField {

	public $required = true;

}

/**
 * Select an entity based on primary key values in hidden input fields, supplied by remote data source.
 */
class EntityField extends InputField {

	private $show_value;

	public function __construct($name, array $primary_key_values, $description, PersistenceModel $model, $show_value, $find_url = null) {
		parent::__construct($name, $primary_key_values, $description, $model);
		$this->show_value = $show_value;
		$this->suggestions[] = $find_url;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// bestaat er een entity met opgegeven primary key?
		$where = array();
		$class = $this->model->orm;
		$orm = new $class();
		foreach ($orm->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		if (!$this->model->exist(implode(' AND ', $where), $this->value)) {
			$this->error = 'Niet gevonden';
		}
		return $this->error === '';
	}

	public function getHtml() {
		// value to show
		if ($this->isPosted()) {
			$show_value = filter_input(INPUT_POST, $this->name . '_show', FILTER_SANITIZE_STRING);
		} else {
			$show_value = $this->show_value;
		}
		$html = '<input name="' . $this->name . '_show" value="' . $show_value . '" origvalue="' . $this->show_value . '"' . $this->getInputAttribute(array('type', 'id', 'class', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete')) . ' />';

		// actual values
		$class = $this->model->orm;
		$orm = new $class();
		foreach ($orm->getPrimaryKey() as $i => $key) {
			$html .= '<input type="hidden" name="' . $this->name . '[]" id="' . $this->getId() . '_' . $key . '" value="' . $this->value[$i] . '" origvalue="' . $this->origvalue[$i] . '" />';
		}
		return $html;
	}

}

abstract class RequiredEntityField extends EntityField {

	public $required = true;

}

/**
 * StudieField
 *
 * Suggereert een aantal studies, doet verder geen controle op invoer.
 */
class StudieField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 100);
		$tustudies = array('BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB');
		// de studies aan de TU, even prefixen met 'TU Delft - '
		$this->suggestions['TU Delft'] = array_map(create_function('$value', 'return "TU Delft - ".$value;'), $tustudies);
		$this->suggestions[] = array('INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden');
	}

}

class EmailField extends TextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check format
		if (!email_like($this->value)) {
			$this->error = 'Ongeldig e-mailadres';
		}
		// check dns record
		else {
			$parts = explode('@', $this->value, 2);
			if (!checkdnsrr($parts[1], 'A') AND ! checkdnsrr($parts[1], 'MX')) {
				$this->error = 'E-mailadres bestaat niet';
			}
		}
		return $this->error === '';
	}

}

class RequiredEmailField extends EmailField {

	public $required = true;

}

/**
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends TextField {

	public function getValue() {
		$this->value = parent::getValue();
		if (startsWith($this->value, CSR_ROOT)) {
			$this->value = str_replace(CSR_ROOT, '', $this->value);
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// controleren of het een geldige url is
		if (!url_like($this->value) AND ! startsWith($this->value, '/')) {
			$this->error = 'Geen geldige url';
		}
		return $this->error === '';
	}

}

class RequiredUrlField extends UrlField {

	public $required = true;

}

class UsernameField extends TextField {

	public function __construct($name, $value) {
		parent::__construct($name, $value, 'Gebruikersnaam');
		$this->title = 'Om mee in te loggen in plaats van het lidnummer.';
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders niet van case kan veranderen
		// doordat usernameExists case-insensitive zoekt
		if (AccountModel::existsUsername($this->value) AND strtolower($this->value) !== strtolower($this->origvalue)) {
			$this->error = 'Deze gebruikersnaam is al in gebruik';
		}
		return $this->error === '';
	}

}

class RequiredUsernameField extends UsernameField {

	public $required = true;

}

class DuckField extends TextField {

	public function __construct($name, $value) {
		parent::__construct($name, $value, 'Duckstad-naam');
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		// doordat duckExists case-insensitive zoekt
		if (ProfielModel::existsDuck($this->value) AND strtolower($this->value) !== strtolower($this->origvalue)) {
			$this->error = 'Deze Duckstad-naam is al in gebruik';
		}
		return $this->error === '';
	}

}

class RequiredDuckField extends DuckField {

	public $required = true;

}

/**
 * Een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */
class TextareaField extends TextField {

	public function __construct($name, $value, $description, $rows = 2, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $max_len, $min_len);
		if (is_int($rows)) {
			$this->rows = $rows;
		}
		$this->css_classes[] = 'AutoSize textarea-transition';
	}

	public function getHtml() {
		return '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete')) . '>' . $this->value . '</textarea>';
	}

	/**
	 * Maakt een verborgen div met dezelfde eigenschappen als de textarea en
	 * gebruikt autoresize eigenschappen van de div om de hoogte te bepalen voor de textarea.
	 * 
	 * @return string
	 */
	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$('#{$this->getId()}').autosize();
JS;
	}

}

class RequiredTextareaField extends TextareaField {

	public $required = true;

}

class WachtwoordField extends TextField {

	public $type = 'password';
	public $enter_submit = true;

}

class RequiredWachtwoordField extends WachtwoordField {

	public $required = true;

}

/**
 * WachtwoordWijzigenField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 * 
 * Bij wachtwoord resetten produceert deze 2 velden.
 */
class WachtwoordWijzigenField extends InputField {

	private $require_current;

	public function __construct($name, Account $account, $require_current = true) {
		$this->require_current = $require_current;
		parent::__construct($name, null, null, $account);
		$this->title = 'Het nieuwe wachtwoord moet langer zijn dan 23 tekens of langer dan 10 en ook hoofdletters, kleine letters, cijfers en speciale tekens bevatten.';

		// blacklist gegevens van account
		$this->blacklist[] = $account->username;
		foreach (explode('@', $account->email) as $email) {
			foreach (explode('.', $email) as $part) {
				if (strlen($part) >= 5) {
					$this->blacklist[] = $part;
				}
			}
		}

		// blacklist gegevens van profiel
		$profiel = $account->getProfiel();
		$this->blacklist[] = $profiel->uid;
		$this->blacklist[] = $profiel->voornaam;
		foreach (explode(' ', $profiel->achternaam) as $part) {
			if (strlen($part) >= 4) {
				$this->blacklist[] = $part;
			}
		}
		$this->blacklist[] = $profiel->postcode;
		$this->blacklist[] = str_replace(' ', '', $profiel->postcode);
		$this->blacklist[] = $profiel->telefoon;
		$this->blacklist[] = $profiel->mobiel;

		// wis lege waarden
		$this->blacklist = array_filter_empty($this->blacklist);

		// algemene blacklist
		$this->blacklist[] = '1234';
		$this->blacklist[] = 'abcd';
		$this->blacklist[] = 'qwerty';
		$this->blacklist[] = 'azerty';
		$this->blacklist[] = 'asdf';
		$this->blacklist[] = 'jkl;';
		$this->blacklist[] = 'password';
		$this->blacklist[] = 'wachtwoord';
	}

	public function isPosted() {
		if ($this->require_current AND ! isset($_POST[$this->name . '_current'])) {
			return false;
		}
		return isset($_POST[$this->name . '_new']) AND isset($_POST[$this->name . '_confirm']);
	}

	public function getValue() {
		if ($this->isPosted()) {
			$this->value = filter_var($_POST[$this->name . '_new'], FILTER_SANITIZE_STRING);
		} else {
			$this->value = false;
		}
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return $this->value;
	}

	public function checkZwarteLijst($pass_plain) {
		foreach ($this->blacklist as $disallowed) {
			if (stripos($pass_plain, $disallowed) !== false) {
				$this->error = htmlspecialchars($disallowed);
				return true;
			}
		}
		return false;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->require_current) {
			$current = filter_input(INPUT_POST, $this->name . '_current', FILTER_SANITIZE_STRING);
		}
		// filter_input does not use current value in $_POST 
		$new = filter_var($_POST[$this->name . '_new'], FILTER_SANITIZE_STRING);
		$confirm = filter_var($_POST[$this->name . '_confirm'], FILTER_SANITIZE_STRING);
		$length = strlen(utf8_decode($new));
		if ($this->require_current AND empty($current)) {
			$this->error = 'U moet uw huidige wachtwoord invoeren';
		} elseif ($this->required AND empty($new)) {
			$this->error = 'U moet een nieuw wachtwoord invoeren';
		} elseif (!$this->require_current OR ! empty($new)) {
			if ($this->require_current AND $current == $new) {
				$this->error = 'Het nieuwe wachtwoord is hetzelfde als het huidige wachtwoord';
			} elseif ($length < 10) {
				$this->error = 'Het nieuwe wachtwoord moet minimaal 10 tekens lang zijn';
			} elseif ($length > 100) {
				$this->error = 'Het nieuwe wachtwoord mag maximaal 100 tekens lang zijn';
			} elseif ($this->checkZwarteLijst($new)) {
				$this->error = 'Het nieuwe wachtwoord of een deel ervan staat op de zwarte lijst: "' . $this->error . '"';
			} elseif (preg_match('/^[0-9]*$/', $new)) {
				$this->error = 'Het nieuwe wachtwoord mag niet uit alleen getallen bestaan';
			} elseif ($length < 23) {
				if (preg_match('/^[a-zA-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook cijfers en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook hoofdletters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9A-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook kleine letters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-zA-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				}
			} elseif (preg_match('/(.)\1\1+/', $new) OR preg_match('/(.{3,})\1+/', $new) OR preg_match('/(.{4,}).*\1+/', $new)) {
				$this->error = 'Het nieuwe wachtwoord bevat teveel herhaling';
			} elseif (empty($confirm)) {
				$this->error = 'Vul uw nieuwe wachtwoord twee keer in';
			} elseif ($new != $confirm) {
				$this->error = 'Nieuwe wachtwoorden komen niet overeen';
			} elseif ($this->require_current AND ! AccountModel::instance()->controleerWachtwoord($this->model, $current)) {
				$this->error = 'Uw huidige wachtwoord is niet juist';
			}
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '';
		if ($this->require_current) {
			$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_current">Huidig wachtwoord' . ($this->require_current ? '<span class="required"> *</span>' : '') . '</label>';
			$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_current" name="' . $this->name . '_current" /></div>';
		}
		$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_new">Nieuw wachtwoord' . ($this->required ? '<span class="required"> *</span>' : '') . '</label>';
		$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_new" name="' . $this->name . '_new" /></div>';
		$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_confirm">Herhaal nieuw wachtwoord' . ($this->required ? '<span class="required"> *</span>' : '') . '</label>';
		$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_confirm" name="' . $this->name . '_confirm" /></div>';
		return $html;
	}

}

class RequiredWachtwoordWijzigenField extends WachtwoordWijzigenField {

	public $required = true;

}
