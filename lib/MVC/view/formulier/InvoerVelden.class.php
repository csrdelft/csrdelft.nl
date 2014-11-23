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
 * 		* RechtenField				Rechten, zie AccessModel
 * 		* LandField					Landen
 * 		* StudieField				Opleidingen
 * 		* EmailField				Email adressen
 * 		* UrlField					Url's
 * 		* TelefoonField				Telefoonnummers
 * 		* TextareaField				Textarea die automagisch uitbreidt bij typen
 * 			- CsrBBPreviewField		Textarea met bbcode voorbeeld
 *  	* NickField					Nicknames
 *  	* DuckField					Ducknames
 *  	* UidField					Uid's  met preview
 * 		* LidField					Leden selecteren
 * 		* IntField					Integers 
 * 		* DecimalField				Kommagetallen
 * 			- BedragField			Bedragen met 2 cijfers achter de komma
 * 	- WachtwoordWijzigenField		Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 * 
 * 
 * Meer uitbreidingen van InputField @see KeuzeVelden.class.php
 * 
 */

/**
 * InputField is de base class van alle FormElements die data leveren,
 * behalve FileField zelf die wel meerdere InputFields bevat.
 */
abstract class InputField implements FormElement, Validator {

	protected $model; // model voor remote data source en validatie
	protected $name; // naam van het veld in POST
	protected $value; // welke initiele waarde heeft het veld?
	protected $origvalue; // welke originele waarde had het veld?
	public $title; // omschrijving bij mouseover title
	public $description; // omschrijving in label
	public $disabled = false; // veld uitgeschakeld?
	public $hidden = false; // veld onzichtbaar voor gebruiker?
	public $readonly = false; // veld mag niet worden aangepast door client?
	public $required = false; // mag het veld leeg zijn?
	public $empty_null = false; // lege waarden teruggeven als null
	public $enter_submit = false; // bij op enter drukken form submitten
	public $preview = true; // preview tonen? (waar van toepassing)
	public $leden_mod = false; // uitzondering leeg verplicht veld voor LEDEN_MOD
	public $autocomplete = true; // browser laten autoaanvullen?
	public $placeholder = null; // plaats een grijze placeholdertekst in leeg veld
	public $error = ''; // foutmelding van dit veld
	public $onchange = null; // callback on change of value
	public $onclick = null; // do on click
	public $onkeydown = null; // prevent illegal character from being entered
	public $onkeyup = null; // respond to keyboard strokes
	public $max_len = 0; // maximale lengte van de invoer
	public $min_len = 0; // minimale lengte van de invoer
	public $rows = 0; // aantal rijen van textarea
	public $css_classes = array('FormElement'); // array met classnames die later in de class-tag komen
	public $suggestions = array(); // array met suggesties die de javascript-autocomplete aan gaat bieden
	public $remotedatasource = ''; // een remotedatasource overruled suggestions
	public $blacklist = null; // array met niet tegestane waarden
	public $whitelist = null; // array met exclusief toegestane waarden

	public function __construct($name, $value, $description, $model = null) {
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
		$this->css_classes[] = $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->getName();
	}

	public function getName() {
		return $this->name;
	}

	public function getId() {
		return 'field_' . $this->getName();
	}

	public function isPosted() {
		return isset($_POST[$this->name]);
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
		// als max_len > 0 dan checken of de lengte er niet boven zit
		if ($this->max_len > 0 AND strlen($this->value) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		// als min_len > 0 dan checken of de lengte er niet onder zit
		if ($this->min_len > 0 AND strlen($this->value) < $this->min_len) {
			$this->error = 'Dit veld moet minimaal ' . $this->min_len . ' tekens lang zijn';
		}
		// als blacklist is gezet dan controleren
		if (is_array($this->blacklist) AND in_array($this->value, $this->blacklist)) {
			$this->error = 'Deze waarde is niet toegestaan';
		}
		// als whitelist is gezet dan controleren
		if (is_array($this->whitelist) AND ! in_array($this->value, $this->whitelist)) {
			$this->error = 'Deze waarde is niet toegestaan';
		}
		return $this->error === '';
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv() {
		$cssclass = 'InputField';
		if ($this->hidden) {
			$cssclass .= ' verborgen';
		}
		if ($this->title) {
			$cssclass .= ' hoverIntent';
		}
		if ($this->error !== '') {
			$cssclass .= ' metFouten';
		}
		return '<div id="wrapper_' . $this->getId() . '" class="' . $cssclass . '" ' . $this->getInputAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	protected function getLabel() {
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
				$help = '<div class="help"><img width="16" height="16" class="icon hoverIntentContent" alt="?" src="' . CSR_PICS . '/famfamfam/help.png"></div>';
			}
			return '<label for="field_' . $this->name . '">' . $help . $this->description . $required . '</label>';
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
			case 'title':
				if ($this->title) {
					return 'title="' . htmlspecialchars($this->title) . '"';
				}
				break;
			case 'disabled':
				if ($this->disabled) {
					return 'disabled';
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
				if ($this->max_len > 0) {
					return 'maxlength="' . $this->max_len . '"';
				}
				break;
			case 'rows':
				if ($this->rows > 0) {
					return 'rows="' . $this->rows . '"';
				}
				break;
			case 'autocomplete':
				if (!$this->autocomplete OR ! empty($this->suggestions) OR ! empty($this->remotedatasource)) {
					return 'autocomplete="off"'; // browser autocompete
				}
				break;
			case 'onchange':
				if ($this->onchange != null) {
					return 'onchange="' . $this->onchange . '"';
				}
				break;
			case 'onclick':
				if ($this->onclick != null) {
					return 'onclick="' . $this->onclick . '"';
				}
				break;
			case 'onkeydown':
				if ($this->onkeydown != null) {
					return 'onkeydown="' . $this->onkeydown . '"';
				}
				break;
			case 'onkeyup':
				if ($this->enter_submit) {
					$this->onkeyup .= <<<JS
if (event.keyCode == 13) { // enter
	this.form.submit();
}
JS;
				}
				if ($this->onkeyup != null) {
					return 'onkeyup="' . $this->onkeyup . '"';
				}
				break;
		}
		return '';
	}

	/**
	 * view die zou moeten werken voor veel velden...
	 */
	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		if ($this->preview) {
			echo $this->getPreviewDiv();
		}
		if ($this->hidden) {
			$type = 'hidden';
		} else {
			$type = 'text';
		}
		echo '<input type="' . $type . '"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup')) . ' />';
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
		if ($this->readonly) {
			return '';
		}
		if (!empty($this->remotedatasource)) {
			$autocomplete = json_encode($this->remotedatasource);
			return <<<JS
$('#{$this->getId()}', form).autocomplete(
	{$autocomplete},
	{
		dataType: "json",
		parse: function(result) { return result; },
		formatItem: function(row, i, n) { return row[0]; },
		clickFire: true,
		max: 20
	}
).result(function(){
	$(this).keyup();
});
JS;
		} elseif (!empty($this->suggestions)) {
			$autocomplete = json_encode($this->suggestions);
			return <<<JS
$('#{$this->getId()}', form).autocomplete(
	{$autocomplete},
	{
		clickFire: true,
		max: 20,
		matchContains: true,
		noRecord: ""
	}
);
JS;
		}
	}

}

/**
 * Een TextField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class TextField extends InputField {

	public function __construct($name, $value, $description, $max_len = 255, $min_len = 0, $model = null) {
		parent::__construct($name, htmlspecialchars_decode($value), $description, $model);
		$this->max_len = (int) $max_len;
		$this->min_len = (int) $min_len;
		if ($this->isPosted()) {
			// reverse InputField constructor $this->getValue()
			$this->value = htmlspecialchars_decode($this->value);
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!is_utf8($this->value)) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst.';
		}
		return $this->error === '';
	}

	public function getValue() {
		$value = parent::getValue();
		if ($this->empty_null AND empty($value)) {
			return null;
		}
		return htmlspecialchars($value);
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
			$this->error = 'Ongeldige bestandsnaam.';
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
		$this->suggestions = array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
	}

}

class RequiredLandField extends LandField {

	public $required = true;

}

class RechtenField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$this->suggestions = AccessModel::instance()->getPermissionSuggestions();
		$this->title = 'Met , en + voor respectievelijk OR en AND. Gebruik | voor OR binnen AND (alsof er haakjes omheen staan)';
		// Gebruik van ! voor negatie en extra : voor functie binnen groep niet vermelden.
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
					// Negatie van een permissie (gebruiker mag deze permissie niet bezitten)
					if (startsWith($perm, '!')) {
						$perm = substr($perm, 1);
					}
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

/**
 * In een UidField kunnen we een uid invullen.
 * Erachter zal dan de naam komen te staan. Het veld valideert als
 *  - het leeg is.
 *  - het een geldig uid bevat.
 */
class UidField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 4, 4);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		if (!Lid::exists($this->value)) {
			$this->error = 'Geen geldig uid opgegeven.';
		}
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getId() . '" class="lidPreview"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}', form).unbind('keyup.autocomplete');
$('#{$this->getId()}', form).bind('keyup.autocomplete', function(event) {
	if ($(this).val().length < 4) {
		$('#lidPreview_{$this->getId()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?uid="+$(this).val(),
	}).done(function(response) {
		$('#lidPreview_{$this->getId()}').html(response);
		init_hoverIntents();
	});
});
JS;
	}

}

/**
 * LidField
 * één lid selecteren zonder een uid te hoeven typen.
 *
 */
class LidField extends TextField {

	// zoekfilter voor door namen2uid gebruikte Zoeker::zoekLeden. 
	// geaccepteerde input: 'leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'
	private $zoekin;

	public function __construct($name, $value, $description, $zoekin = 'leden') {
		$naam = Lid::naamLink($value, 'full', 'plain');
		if ($naam !== false) {
			$value = $naam;
		}
		parent::__construct($name, $value, $description);
		if (!in_array($zoekin, array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'))) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->remotedatasource = '/tools/naamsuggesties/' . $this->zoekin;
	}

	/**
	 * LidField::getValue() levert altijd een uid of '' op.
	 */
	public function getValue() {
		// leeg veld direct teruggeven
		if (parent::getValue() == '') {
			return '';
		}
		// uid opzoeken
		if ($uid = namen2uid(parent::getValue(), $this->zoekin) AND isset($uid[0]['uid'])) {
			return $uid[0]['uid'];
		}
		return '';
	}

	/**
	 * checkt of er een uniek lid wordt gevonden
	 */
	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$uid = namen2uid(parent::getValue(), $this->zoekin);
		if ($uid) {
			if (isset($uid[0]['uid']) AND Lid::exists($uid[0]['uid'])) {
				return true;
			} elseif (count($uid[0]['naamOpties']) > 0) { // meerdere naamopties?
				$this->error = 'Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error = 'Geen geldig lid';
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getId() . '" class="lidPreview"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}', form).unbind('keyup.autocomplete');
$('#{$this->getId()}', form).bind('keyup.autocomplete', function(event) {
	if ($(this).val().length < 1) {
		$('#lidPreview_{$this->getId()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?naam="+$(this).val()+"&zoekin={$this->zoekin}",
	}).done(function(response) {
		$('#lidPreview_{$this->getId()}').html(response);
		init_hoverIntents();
	});
});
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

	public function __construct($name, array $primary_key_values, $description, PersistenceModel $model, $show_value, $remotedatasource = null) {
		parent::__construct($name, $primary_key_values, $description, $model);
		$this->show_value = $show_value;
		$this->remotedatasource = $remotedatasource;
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

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		// value to show
		if ($this->isPosted()) {
			$show_value = filter_input(INPUT_POST, $this->name . '_show', FILTER_SANITIZE_STRING);
		} else {
			$show_value = $this->show_value;
		}
		echo '<input type="text" name="' . $this->name . '_show" id="' . $this->getId() . '" value="' . $show_value . '" origvalue="' . $this->show_value . '"' . $this->getInputAttribute(array('class', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup')) . ' />';

		// actual values
		$class = $this->model->orm;
		$orm = new $class();
		foreach ($orm->getPrimaryKey() as $i => $key) {
			echo '<input type="hidden" name="' . $this->name . '[]" id="' . $this->getId() . '_' . $key . '" value="' . $this->value[$i] . '" origvalue="' . $this->origvalue[$i] . '" />';
		}

		echo '</div>';
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

		// de studies aan de TU, even prefixen met 'TU Delft - '
		$tustudies = array('BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB');
		$tustudies = array_map(create_function('$value', 'return "TU Delft - ".$value;'), $tustudies);

		$andere = array('INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden');

		$this->suggestions = array_merge($tustudies, $andere);
	}

}

class EmailField extends TextField {

	/**
	 * Dikke valideerfunctie voor emails.
	 */
	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// bevat het email-adres een @
		if (strpos($this->value, '@') === false) {
			$this->error = 'Ongeldig formaat email-adres';
		} else {
			# anders gaan we m ontleden en controleren
			list ($usr, $dom) = explode('@', $this->value);
			if (mb_strlen($usr) > 50) {
				$this->error = 'Gebruik max. 50 karakters voor de @:';
			} elseif (mb_strlen($dom) > 50) {
				$this->error = 'Gebruik max. 50 karakters na de @:';
				# RFC 821 <- voorlopig voor JabberID even zelfde regels aanhouden
				# http:// www.lookuptables.com/
				# Hmmmz, \x2E er uit gehaald ( . )
			} elseif (preg_match('/[^\x21-\x7E]/', $usr) OR preg_match('/[\x3C\x3E\x28\x29\x5B\x5D\x5C\x2C\x3B\x40\x22]/', $usr)) {
				$this->error = 'Het adres bevat ongeldige karakters voor de @:';
			} elseif (!preg_match('/^[a-z0-9]+([-.][a-z0-9]+)*\\.[a-z]{2,4}$/i', $dom)) {
				$this->error = 'Het domein is ongeldig:';
			} elseif (!checkdnsrr($dom, 'A') and ! checkdnsrr($dom, 'MX')) {
				$this->error = 'Het domein bestaat niet (IPv4):';
			} elseif (!checkdnsrr($dom, 'MX')) {
				$this->error = 'Het domein is niet geconfigureerd om email te ontvangen:';
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

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// controleren of het een geldige url is
		if (!url_like($this->value)) {
			$this->error = 'Geen geldige url';
		}
		return $this->error === '';
	}

}

class RequiredUrlField extends UrlField {

	public $required = true;

}

/**
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends TextField {

	public $min = null;
	public $max = null;
	public $min_alert = null;
	public $max_alert = null;

	public function __construct($name, $value, $description, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		if ($min !== null) {
			$this->min = (int) $min;
			$this->min_alert = 'Minimaal ' . $this->min;
		}
		if ($max !== null) {
			$this->max = (int) $max;
			$this->max_alert = 'Maximaal ' . $this->max;
		}
		$this->onkeydown = <<<JS

if (event.keyCode == 107 || event.keyCode == 109) { // + -
	event.preventDefault();
	return false;
}
JS;
		$this->onkeyup = <<<JS
if (event.keyCode == 107) { // +
	$('#add_{$this->getId()}').trigger('click');
}
else if (event.keyCode == 109) { // -
	$('#substract_{$this->getId()}').trigger('click');
}
JS;
	}

	public function getValue() {
		$value = parent::getValue();
		if ($value == '') {
			return null;
		}
		return (int) $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!preg_match('/\d+/', $this->value)) {
			$this->error = 'Alleen gehele getallen toegestaan';
		} elseif ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
			// exception for leden mod
		} elseif ($this->min !== null AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

	/**
	 * view die zou moeten werken voor veel velden...
	 */
	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		if ($this->preview) {
			echo $this->getPreviewDiv();
		}
		if ($this->hidden) {
			$type = 'hidden';
		} else {
			$type = 'text';

			if (!$this->readonly AND ! $this->disabled AND ! $this->hidden) {
				if ($this->min !== null AND $this->getValue() === $this->min) {
					$class = 'class="disabled"';
				} else {
					$class = '';
				}
				$minus = CSR_PICS . '/knopjes/min.png';
				echo <<<HTML
<span id="substract_{$this->getId()}" {$class} style="cursor:pointer;padding:7px;"><img src="{$minus}" alt="-" class="icon" width="20" height="20" /></span>
HTML;
			}
		}

		if ($this->min !== null) {
			if ($this->min_alert) {
				$alert = "alert('{$this->min_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS
if (parseInt( $(this).val() ) < {$this->min}) {
	{$alert}
	$(this).val({$this->min});
	$('#substract_{$this->getId()}').addClass('disabled');
} else {
	$('#substract_{$this->getId()}').removeClass('disabled');
}
JS;
		}
		if ($this->max !== null) {
			if ($this->max_alert) {
				$alert = "alert('{$this->max_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS
if (parseInt( $(this).val() ) > {$this->max}) {
	{$alert}
	$(this).val({$this->max});
	$('#add_{$this->getId()}').addClass('disabled');
} else {
	$('#add_{$this->getId()}').removeClass('disabled');
}
JS;
		}

		echo ' <input type="' . $type . '"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup')) . ' /> ';

		if (!$this->readonly AND ! $this->disabled AND ! $this->hidden) {
			if ($this->max !== null AND $this->getValue() === $this->max) {
				$class = 'class="disabled"';
			} else {
				$class = '';
			}
			$plus = CSR_PICS . '/knopjes/plus.png';
			echo <<<HTML
<span id="add_{$this->getId()}" {$class} style="cursor:pointer;padding:7px;"><img src="{$plus}" alt="+" class="icon" width="20" height="20" /></span>
HTML;
		}
		echo '</div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#add_{$this->getId()}').click(function() {
	if ($(this).hasClass('disabled')) {
		return;
	}
	$('#{$this->getId()}').val(parseInt($('#{$this->getId()}').val()) + 1).trigger('onchange');
});
$('#substract_{$this->getId()}').click(function() {
	if ($(this).hasClass('disabled')) {
		return;
	}
	$('#{$this->getId()}').val(parseInt($('#{$this->getId()}').val()) - 1).trigger('onchange');
});
JS;
	}

}

class RequiredIntField extends IntField {

	public $required = true;

}

/**
 * Invoeren van een decimaal getal. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class DecimalField extends TextField {

	public $precision;
	public $min = null;
	public $max = null;

	public function __construct($name, $value, $description, $precision, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		$this->precision = $precision;
		if ($min !== null) {
			$this->min = (float) $min;
		}
		if ($max !== null) {
			$this->max = (float) $max;
		}
	}

	public function getValue() {
		$value = parent::getValue();
		if ($value == '') {
			return null;
		}
		return round((float) str_replace(',', '.', $value), $this->precision);
	}

	/**
	 * Some tricky typecasting is going on.
	 * 
	 * @return boolean
	 */
	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!preg_match('/^(\d+.\d{' . $this->precision . '})$/', parent::getValue())) {
			$this->error = 'Voer precies ' . $this->precision . ' decimalen in';
		} elseif ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->min !== null AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

}

class RequiredDecimalField extends DecimalField {

	public $required = true;

}

/**
 * Invoeren van een bedrag in centen, dus precisie van 2 cijfers achter de komma.
 * 
 */
class BedragField extends DecimalField {

	public $valuta;

	public function __construct($name, $value, $description, $valuta = '€', $min = null, $max = null) {
		parent::__construct($name, $value / 100, $description, 2, $min, $max);
		$this->valuta = $valuta;
	}

	public function getValue() {
		$value = parent::getValue();
		if ($this->empty_null AND empty($value)) {
			return null;
		}
		return (int) $value * 100;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		if ($this->preview) {
			echo $this->getPreviewDiv();
		}
		if ($this->hidden) {
			$type = 'hidden';
		} else {
			$type = 'text';
		}
		echo $this->valuta . ' <input type="' . $type . '"' . $this->getInputAttribute(array('id', 'name', 'class', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup'));
		echo ' value="' . number_format(str_replace(',', '.', $this->value), $this->precision, ',', '') . '" origvalue="';
		// if an error occured do not re-format the original value
		// prevent unchanged is not smart enough for rounding and such
		if ($this->getError() != '') {
			echo $this->origvalue;
		} else {
			echo number_format(str_replace(',', '.', $this->origvalue), $this->precision, ',', '');
		}
		echo '" /></div>';
	}

}

class RequiredBedragField extends DecimalField {

	public $required = true;

}

/**
 * NickField
 *
 * is pas valid als dit lid de enige is met deze nick.
 */
class NickField extends TextField {

	public $max_len = 20;

	public function __construct($name, $value, $description, Lid $lid) {
		parent::__construct($name, $value, $description, 255, 0, $lid);
	}

	public function validate() {
		if (!$this->model instanceof Lid) {
			throw new Exception($this->getType() . ' moet een Lid-object meekrijgen');
		}
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		// omdat this->nickExists in mysql case-insensitive zoek
		if (Lid::nickExists($this->value) AND strtolower($this->model->getNickname()) != strtolower($this->value)) {
			$this->error = 'Deze bijnaam is al in gebruik.';
		}
		return $this->error === '';
	}

}

/**
 * DuckField
 *
 * is pas valid als dit lid de enige is met deze duckname.
 * 
 * COPY-PASTE from NickField
 */
class DuckField extends TextField {

	public $max_len = 20;

	public function __construct($name, $value, $description, Lid $lid) {
		parent::__construct($name, $value, $description, 255, 0, $lid);
	}

	public function validate() {
		if (!$this->model instanceof Lid) {
			throw new Exception($this->getType() . ' moet een Lid-object meekrijgen');
		}
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		// omdat this->nickExists in mysql case-insensitive zoek
		if (Lid::duckExists($this->value) AND strtolower($this->model->getDuckname()) != strtolower($this->value)) {
			$this->error = 'Deze Duckstad-naam is al in gebruik.';
		}
		return $this->error === '';
	}

}

/**
 * TelefoonField
 *
 * is valid als er een enigszins op een telefoonnummer lijkende string wordt
 * ingegeven.
 */
class TelefoonField extends TextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		if (!preg_match('/^([\d\+\-]{10,20})$/', $this->value)) {
			$this->error = 'Geen geldig telefoonnummer.';
		}
		return $this->error === '';
	}

}

/**
 * Een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */
class TextareaField extends TextField {

	public function __construct($name, $value, $description, $rows = 3, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $max_len, $min_len);
		$this->rows = (int) $rows;
		$this->css_classes[] = 'AutoSize textarea-transition';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		echo $this->getPreviewDiv();
		echo '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup')) . '>' . $this->value . '</textarea>';
		echo '</div>';
	}

	/**
	 * Maakt een verborgen div met dezelfde eigenschappen als de textarea en
	 * gebruikt autoresize eigenschappen van de div om de hoogte te bepalen voor de textarea.
	 * 
	 * @override parent
	 * @return string
	 */
	public function getJavascript() {
		return <<<JS
$('#{$this->getId()}', form).autosize();
JS;
	}

}

class RequiredTextareaField extends TextareaField {

	public $required = true;

}

/**
 * Textarea met een bbcode-preview erbij.
 */
class CsrBBPreviewField extends TextareaField {

	public $previewOnEnter = false;

	public function __construct($name, $value, $description, $rows = 5, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $rows, $max_len, $min_len);
	}

	public function getPreviewDiv() {
		return <<<HTML
<div class="float-right">
	<a href="http://csrdelft.nl/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a>
	<input type="button" value="Voorbeeld" onclick="CsrBBPreview('{$this->getId()}', '{$this->getName()}Preview');"/>
</div>
<br />
<div id="{$this->getName()}Preview" class="preview"></div>
HTML;
	}

	public function getJavascript() {
		if (!$this->previewOnEnter) {
			return parent::getJavascript();
		}
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}', form).unbind('keyup.preview');
$('#{$this->getId()}', form).bind('keyup.preview', function(event) {
	if(event.keyCode === 13) { // enter
		CsrBBPreview('{$this->getId()}', '{$this->getName()}Preview');
	}
});
JS;
	}

}

class RequiredCsrBBPreviewField extends CsrBBPreviewField {

	public $required = true;

}

class WachtwoordField extends TextField {

	public $enter_submit = true;

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();
		echo '<input type="password"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick', 'onkeydown', 'onkeyup')) . ' />';
		echo '</div>';
	}

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

	private $reset;

	public function __construct($name, Lid $lid, $reset = false) {
		parent::__construct($name, $name, null, $lid);
		$this->reset = $reset;
	}

	public function isPosted() {
		$return = true;
		if (!$this->reset) {
			$return = isset($_POST[$this->name . '_current']);
		}
		return $return AND isset($_POST[$this->name . '_new'], $_POST[$this->name . '_confirm']);
	}

	public function getValue() {
		if ($this->isPosted()) {
			$value = $_POST[$this->name . '_new'];
		} else {
			$value = false;
		}
		if ($this->empty_null AND empty($value)) {
			return null;
		}
		return $value;
	}

	public function validate() {
		if (!$this->model instanceof Lid) {
			throw new Exception($this->getType() . ' moet een Lid-object meekrijgen');
		}
		if (!parent::validate()) {
			return false;
		}
		if (!$this->reset) {
			$current = filter_input(INPUT_POST, $this->name . '_current', FILTER_SANITIZE_STRING);
		}
		$new = filter_input(INPUT_POST, $this->name . '_new', FILTER_SANITIZE_STRING);
		$confirm = filter_input(INPUT_POST, $this->name . '_confirm', FILTER_SANITIZE_STRING);
		$length = strlen(utf8_decode($new));
		if (!$this->reset AND ! empty($new) AND empty($current)) {
			$this->error = 'U dient uw huidige wachtwoord ook in te voeren';
		} elseif ($this->reset OR ! empty($new)) {
			if (preg_match('/^[0-9]*$/', $new)) {
				$this->error = 'Het nieuwe wachtwoord moet ook letters of leestekens bevatten';
			} elseif (preg_match('/^[a-zA-Z]*$/', $new)) {
				$this->error = 'Het nieuwe wachtwoord moet ook cijfers of leestekens bevatten';
			} elseif ($length < 8 OR $length > 16) {
				$this->error = 'Het wachtwoord moet minimaal 8 en maximaal 16 tekens lang zijn';
			} elseif (empty($confirm)) {
				$this->error = 'Vul uw nieuwe wachtwoord twee keer in';
			} elseif ($new != $confirm) {
				$this->error = 'Nieuwe wachtwoorden komen niet overeen';
			} elseif (!$this->reset AND ! checkpw($this->model, $current)) {
				$this->error = 'Uw huidige wachtwoord is niet juist';
			}
		}
		return $this->error === '';
	}

	public function view() {
		echo $this->getDiv();
		echo '<div class="password">';
		echo $this->getErrorDiv();
		if (!$this->reset) {
			echo '<label for="' . $this->name . '_current">Huidige wachtwoord</label>';
			echo '<input type="password" autocomplete="off" id="' . $this->getId() . '_current" name="' . $this->name . '_current" /></div>';
		}
		echo '<div class="password"><label for="' . $this->name . '_new">Nieuw wachtwoord</label>';
		echo '<input type="password" autocomplete="off" id="' . $this->getId() . '_new" name="' . $this->name . '_new" /></div>';
		echo '<div class="password"><label for="' . $this->name . '_confirm">Nogmaals</label>';
		echo '<input type="password" autocomplete="off" id="' . $this->getId() . '_confirm" name="' . $this->name . '_confirm" /></div>';
		echo '</div>';
	}

	/**
	 * @override parent
	 * @return string
	 */
	public function getJavascript() {
		return '';
	}

}
