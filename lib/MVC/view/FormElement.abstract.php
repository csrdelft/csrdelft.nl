<?php

/**
 * FormElement.abstract.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Dit is een poging om maar op één plek dingen voor een formulier te defenieren:
 *  - validatorfuncties
 *  - Html voor de velden, inclusief bijbehorende javascript.
 *  - suggesties voor formuliervelden
 * 
 * Alle elementen die in een formulier terecht kunnen komen stammen af van
 * de class FormElement.
 * 
 * FormElement
 *  - InputField					Elementen die data leveren
 *  - SubmitButton					Submitten en resetten van het formulier
 *  - HtmlComment					Uitleg/commentaar in een formulier stoppen
 *  - FileField						Bestand upload ketzer
 * 
 * Uitbreidingen van InputField:
 * 	- TextField						Simpele input
 * 		* LandField					Landjes 
 * 		* StudieField				Studie
 * 		* EmailField				Email adressen
 * 		* UrlField					Urls
 * 		* TelefoonField				Telefoonnummers
 * 		* TextareaField					Textarea
 * 			- UbbPreviewField			Textarea met ubb voorbeeld 
 * 			- AutoresizeTextareaField	Textarea die automagisch uitbreidt bij typen
 *  	* NickField					Nicknames
 *  	* DuckField					Ducknames
 *  	* UidField					Uid's  met preview
 * 		* LidField					Leden selecteren
 * 		* IntField					Integers 
 * 		* FloatField				Bedragen
 * 	- PassField						Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 * 	- SelectField
 * 		* GeslachtField				m/v
 * 		* JaNeeField				ja/nee
 * 		* VerticaleField			Verticalen
 * 		* KerkField					Denominaties
 * 	- DatumField					Datums (want data is zo ambigu)
 * 	- TijdField						Tijsstip
 * 
 * Uitbreidingen van HtmlComment:
 * 		- HtmlComment				invoer wordt als html weergegeven
 * 		- UbbComment				invoer wordt als ubb geparsed
 * 		- Subkopje					invoer wordt als <h3> weergegeven
 * 
 */
interface FormElement extends View {

	public function getModel();

	public function getType();

	public function getJavascript();
}

/**
 * InputField is de base class van alle FormElements die data leveren,
 * behalve FileField zelf die wel meerdere InputFields bevat.
 */
abstract class InputField implements FormElement, Validator {

	protected $model;
	protected $name;  //naam van het veld in POST
	protected $value;  //welke initiele waarde heeft het veld?
	protected $origvalue; //welke originele waarde had het veld?
	public $title;  //omschrijving bij mouseover title
	public $description; //omschrijving in label
	public $disabled = false;   //veld uitgeschakeld?
	public $notnull = false; //mag het veld leeg zijn?
	public $leden_mod = false; //uitzondering leeg verplicht veld voor LEDEN_MOD
	public $autocomplete = true;   //browser laten autoaanvullen?
	public $placeholder = null;  //plaats een grijze placeholdertekst in leeg veld
	public $error = ''; //foutmelding van dit veld
	public $onchange = null;   //javascript onChange
	public $onclick = null;   //javascript onClick
	public $max_len = 0; //maximale lengte van de invoer
	public $min_len = 0; //minimale lengte van de invoer
	public $rows = 0;  //aantal rijen van textarea
	protected $css_classes = array('FormField'); //array met classnames die later in de class-tag komen
	protected $suggestions = array(); //array met suggesties die de javascript-autocomplete aan gaat bieden
	protected $remotedatasource = '';

	public function __construct($name, $value, $description = null, $model = null) {
		$this->model = $model;
		$this->name = $name;
		if ($this->isPosted()) {
			$this->value = $this->getValue();
		} else {
			$this->value = $value;
		}
		$this->origvalue = $value;
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

	/**
	 * Een remotedatasource overruled suggestions
	 */
	public function setRemoteSuggestionsSource($url) {
		$this->remotedatasource = $url;
	}

	public function setSuggestions(array $array) {
		$this->suggestions = $array;
	}

	public function setPlaceholder($string) {
		$this->placeholder = $string;
	}

	public function setOnChangeScript($javascript) {
		$this->onchange = $javascript;
	}

	public function setOnClickScript($javascript) {
		$this->onclick = $javascript;
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
		//vallen over lege velden als dat aangezet is voor het veld
		//(tenzij gebruiker LEDEN_MOD heeft en deze optie aan staat voor dit veld)
		if (!$this->isPosted()) {
			$this->error = 'Veld is niet gepost';
		} elseif ($this->value === '' AND $this->notnull) {
			if ($this->leden_mod AND LoginLid::mag('P_LEDEN_MOD')) {
				// exception for leden mod
			} else {
				$this->error = 'Dit is een verplicht veld';
			}
		}
		//als max_len > 0 dan checken of de lengte er niet boven zit
		if ($this->max_len > 0 AND strlen($this->value) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		//als min_len > 0 dan checken of de lengte er niet onder zit
		if ($this->min_len > 0 AND strlen($this->value) < $this->min_len) {
			$this->error = 'Dit veld moet minimaal ' . $this->min_len . ' tekens lang zijn';
		}
		return $this->error === '';
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv() {
		$cssclass = 'InputField';
		if ($this->title) {
			$cssclass .= ' hoverIntent';
		}
		if ($this->error !== '') {
			$cssclass .= ' metFouten';
		}
		return '<div class="' . $cssclass . '" ' . $this->getInputAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	protected function getLabel() {
		if (!empty($this->description)) {
			$required = '';
			if ($this->notnull) {
				if ($this->leden_mod AND LoginLid::mag('P_LEDEN_MOD')) {
					// exception for leden mod
				} else {
					$required = '<span class="required"> *</span>';
				}
			}
			$help = '';
			if ($this->title) {
				$help = '<div class="help"><img width="16" height="16" class="icon hoverIntentContent" alt="?" src="http://plaetjes.csrdelft.nl/famfamfam/help.png"></div>';
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
		if ($this->notnull) {
			if ($this->leden_mod AND LoginLid::mag('P_LEDEN_MOD')) {
				// exception for leden mod
			} else {
				$this->css_classes[] = 'required';
			}
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
					return 'title="' . $this->title . '"';
				}
				break;
			case 'disabled':
				if ($this->disabled) {
					return 'disabled';
				}
				break;
			case 'placeholder':
				if ($this->placeholder != null) {
					return 'placeholder="' . $this->placeholder . '"';
				}
				break;
			case 'rows':
				if ($this->rows > 0) {
					return 'rows="' . $this->rows . '"';
					break;
				}
				break;
			case 'maxlength':
				if ($this->max_len > 0) {
					return 'maxlength="' . $this->max_len . '"';
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
		echo '<input type="text"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . ' />';
		echo $this->getPreviewDiv();
		//afsluiten van de div om de hele tag heen.
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
$('#{$this->getId()}', form).autocomplete({
	source: {$autocomplete},
	clickFire: true,
	max: 20,
	matchContains: true
});
JS;
		}
	}

}

/**
 * Verborgen veld voor de gebruiker.
 */
class HiddenField extends InputField {

	public function view() {
		echo '<input type="hidden"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'maxlength', 'placeholder', 'autocomplete')) . ' />';
	}

}

/**
 * Een TextField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class TextField extends InputField {

	public function __construct($name, $value, $description, $max_len = 255, $min_len = 0, $model = null) {
		parent::__construct($name, $value, $description, $model);
		$this->max_len = (int) $max_len;
		$this->min_len = (int) $min_len;
		$this->value = htmlspecialchars_decode($this->value);
		$this->origvalue = htmlspecialchars_decode($value);
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
		return htmlspecialchars(parent::getValue());
	}

}

class RequiredTextField extends TextField {

	public $notnull = true;

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

	public $notnull = true;

}

/**
 * LandField met een aantal autocomplete suggesties voor landen.
 * Doet verder geen controle op niet-bestaande landen...
 */
class LandField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$landSuggesties = array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
		$this->setSuggestions($landSuggesties);
	}

}

class RequiredLandField extends LandField {

	public $notnull = true;

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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		if (!Lid::exists($this->value)) {
			$this->error = 'Geen geldig uid opgegeven.';
		}
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getName() . '" class="lidPreview"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}', form).unbind('keyup.autocomplete');
$('#{$this->getId()}', form).bind('keyup.autocomplete', function(event) {
	if ($(this).val().length < 4) {
		$('#lidPreview_{$this->getName()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?uid="+$(this).val(),
	}).done(function(response) {
		$('#lidPreview_{$this->getName()}').html(response);
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

	public function __construct($name, $value, $description = null, $zoekin = 'leden') {
		$naam = Lid::naamLink($value, 'full', 'plain');
		if ($naam !== false) {
			$value = $naam;
		}
		parent::__construct($name, $value, $description);
		if (!in_array($zoekin, array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'))) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->setRemoteSuggestionsSource('/tools/naamsuggesties/' . $this->zoekin);
	}

	/**
	 * LidField::getValue() levert altijd een uid of '' op.
	 */
	public function getValue() {
		//leeg veld meteen teruggeven
		if (parent::getValue() === '') {
			return '';
		}
		//uid opzoeken
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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		$uid = namen2uid(parent::getValue(), $this->zoekin);
		if ($uid) {
			if (isset($uid[0]['uid']) AND Lid::exists($uid[0]['uid'])) {
				return true;
			} elseif (count($uid[0]['naamOpties']) > 0) { //meerdere naamopties?
				$this->error = 'Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error = 'Geen geldig lid';
		return $this->error === '';
	}

	public function getPreviewDiv() {
		return '<div id="lidPreview_' . $this->getName() . '" class="lidPreview"></div>';
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}', form).unbind('keyup.autocomplete');
$('#{$this->getId()}', form).bind('keyup.autocomplete', function(event) {
	if ($(this).val().length < 1) {
		$('#lidPreview_{$this->getName()}').html('');
		return;
	}
	$.ajax({
		url: "/tools/naamlink.php?naam="+$(this).val()+"&zoekin={$this->zoekin}",
	}).done(function(response) {
		$('#lidPreview_{$this->getName()}').html(response);
		init_hoverIntents();
	});
});
JS;
	}

}

class RequiredLidField extends LidField {

	public $notnull = true;

}

/**
 * StudieField
 *
 * Suggereert een aantal studies, doet verder geen controle op invoer.
 */
class StudieField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 100);

		//de studies aan de TU, even prefixen met 'TU Delft - '
		$tustudies = array('BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB');
		$tustudies = array_map(create_function('$value', 'return "TU Delft - ".$value;'), $tustudies);

		$andere = array('INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden');

		$this->setSuggestions(array_merge($tustudies, $andere));
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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		//bevat het email-adres een @
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
				# http://www.lookuptables.com/
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

	public $notnull = true;

}

/**
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends TextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		// controleren of het een geldige url is...
		if (!is_utf8($this->value) OR ! preg_match('#([\w]+?://[^ "\n\r\t<]*?)#is', $this->value)) {
			$this->error = 'Ongeldige karakters';
		}
		return $this->error === '';
	}

}

/**
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends TextField {

	public $min = null;
	public $max = null;

	public function __construct($name, $value, $description, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		if ($min !== null) {
			$this->min = (int) $min;
		}
		if ($max !== null) {
			$this->max = (int) $max;
		}
	}

	public function getValue() {
		if (!$this->notnull AND parent::getValue() === '') {
			return null;
		}
		return (int) parent::getValue();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		//parent checks notnull
		if (parent::getValue() === '') {
			return true;
		} elseif (!preg_match('/\d+/', parent::getValue())) {
			$this->error = 'Alleen getallen toegestaan';
		} elseif ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} else if ($this->leden_mod AND LoginLid::mag('P_LEDEN_MOD')) {
			// exception for leden mod
		} elseif ($this->min !== null AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

}

class RequiredIntField extends IntField {

	public $notnull = true;

}

/**
 * Invoeren van een float. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class FloatField extends TextField {

	public $min = null;
	public $max = null;

	public function __construct($name, $value, $description, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		if ($min !== null) {
			$this->min = (float) $min;
		}
		if ($max !== null) {
			$this->max = (float) $max;
		}
	}

	public function getValue() {
		return (float) str_replace(',', '.', parent::getValue());
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		//parent checks notnull
		if (parent::getValue() === '') {
			return true;
		} elseif (!preg_match('/\d+(,{1}\d*)?/', str_replace('.', ',', parent::getValue()))) {
			$this->error = 'Alleen komma-getallen toegestaan';
		} elseif ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->min !== null AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

}

class RequiredFloatField extends FloatField {

	public $notnull = true;

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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		//check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		//omdat this->nickExists in mysql case-insensitive zoek
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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		//check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		//omdat this->nickExists in mysql case-insensitive zoek
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
		//parent checks notnull
		if ($this->value === '') {
			return true;
		}
		if (!preg_match('/^([\d\+\-]{10,20})$/', $this->value)) {
			$this->error = 'Geen geldig telefoonnummer.';
		}
		return $this->error === '';
	}

}

/**
 * Een TextField levert een textarea.
 */
class TextareaField extends TextField {

	public function __construct($name, $value, $description = null, $rows = 5, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $max_len, $min_len);
		$this->rows = (int) $rows;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'rows', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . '>' . $this->value . '</textarea>';

		echo $this->getPreviewDiv();
		echo '</div>';
	}

}

class RequiredTextareaField extends TextareaField {

	public $notnull = true;

}

/**
 * Een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */
class AutoresizeTextareaField extends TextareaField {

	public function __construct($name, $value, $description = null, $rows = 1, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $rows, $max_len, $min_len);
		$this->css_classes[] = 'wantsAutoresize';
	}

	/**
	 * Maakt een verborgen div met dezelfde eigenschappen als de textarea en
	 * gebruikt autoresize eigenschappen van de div om de hoogte te bepalen voor de textarea.
	 */
	public function getJavascript() {
		//FIXME: incompatible with repeated form_ready_init
		return <<<JS
$('.wantsAutoresize', form).each(function(){
	var textarea = $(this);
	var fieldname = textarea.attr('id').substring(6);
	var hiddenDiv = $(document.createElement('div'));
	var content = null;

	hiddenDiv.addClass('hiddendiv '+fieldname)
		.css({
			font-size: textarea.css('font-size'),
			font-weight: textarea.css('font-size'),
			width: textarea.css('width'),
			word-break: "break-all",
			visibility: "hidden"
		});
	$('body').append(hiddenDiv);

	textarea.unbind('keyup.preview');
	textarea.bind('keyup.preview', function(event) {
		content = textarea.val();
		content = content.replace('<', 'X');
		content = content.replace(/\\n/g, '<br>');
		hiddenDiv.html(content+'<br><br>');
		textarea.css('height', hiddenDiv.height());
	});
});
JS;
	}

}

class RequiredAutoresizeTextField extends AutoresizeTextareaField {

	public $notnull = true;

}

/**
 * Textarea met een ubb-preview erbij.
 */
class UbbPreviewField extends AutoresizeTextareaField {

	public $previewOnEnter = false;

	public function __construct($name, $value, $description = null, $rows = 5, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $rows, $max_len, $min_len);
	}

	public function getPreviewDiv() {
		return <<<HTML
<div style="float: right;">
	<br />
	<a style="float: right;" class="knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
	<br /><br />
	<input type="button" value="Voorbeeld" onclick="ubbPreview('{$this->getId()}', '{$this->getName()}Preview');"/>
</div>
<div id="{$this->getName()}Preview" class="preview"></div>
HTML;
	}

	public function getJavascript() {
		if (!$this->previewOnEnter) {
			return '';
		}
		return <<<JS
$('#{$this->getId()}', form).unbind('keyup.preview');
$('#{$this->getId()}', form).bind('keyup.preview', function(event) {
	if(event.keyCode === 13) { //enter
		ubbPreview('{$this->getId()}', '{$this->getName()}Preview');
	}
});
JS;
	}

}

class RequiredUbbPreviewField extends UbbPreviewField {

	public $notnull = true;

}

/**
 * PassField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 */
class PassField extends InputField {

	public function __construct($name, Lid $lid) {
		parent::__construct($name, $name, null, $lid);
	}

	public function isPosted() {
		return isset($_POST[$this->name . '_current'], $_POST[$this->name . '_new'], $_POST[$this->name . '_confirm']);
	}

	public function getValue() {
		if ($this->isPosted()) {
			return $_POST[$this->name . '_new'];
		}
		return false;
	}

	public function validate() {
		if (!$this->model instanceof Lid) {
			throw new Exception($this->getType() . ' moet een Lid-object meekrijgen');
		}
		if (!parent::validate()) {
			return false;
		}
		$current = $_POST[$this->name . '_current'];
		$new = $_POST[$this->name . '_new'];
		$confirm = $_POST[$this->name . '_confirm'];
		if ($current != '') {
			if (!$this->model->checkpw($current)) {
				$this->error = 'Uw huidige wachtwoord is niet juist';
			} else {
				if ($new === '' OR $confirm === '') {
					$this->error = 'Vul uw nieuwe wachtwoord twee keer in';
				} elseif ($new != $confirm) {
					$this->error = 'Nieuwe wachtwoorden komen niet overeen';
				} elseif (preg_match('/^[0-9]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook letters of leestekens bevatten... :-|';
				} elseif (mb_strlen($new) < 6 OR mb_strlen($new) > 60) {
					$this->error = 'Het wachtwoord moet minimaal 6 en maximaal 16 tekens bevatten';
				}
			}
		}
		if ($new != '' AND $current === '') {
			$this->error = 'U dient uw huidige wachtwoord ook in te voeren';
		}
		return $this->error === '';
	}

	public function view() {
		echo $this->getDiv();
		echo '<div class="password">';
		echo $this->getErrorDiv();
		echo '<label for="field_' . $this->name . '_current">Huidige wachtwoord</label>';
		echo '<input type="password" autocomplete="off" id="field_' . $this->name . '_current" name="' . $this->name . '_current" /></div>';
		echo '<div class="password"><label for="field_' . $this->name . '_new">Nieuw wachtwoord</label>';
		echo '<input type="password" autocomplete="off" id="field_' . $this->name . '_new" name="' . $this->name . '_new" /></div>';
		echo '<div class="password"><label for="field_' . $this->name . '_confirm">Nogmaals</label>';
		echo '<input type="password" autocomplete="off" id="field_' . $this->name . '_confirm" name="' . $this->name . '_confirm" /></div>';
		echo '</div>';
	}

	public function getJavascript() {
		return '';
	}

}

/**
 * SelectField
 * Basis html-select met een aantal opties.
 *
 * is valid als één van de opties geselecteerd is //TODO: of meerdere
 */
class SelectField extends InputField {

	public $options;
	public $size;
	public $multiple; //TODO

	public function __construct($name, $value, $description, array $options, $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->size = (int) $size;
		$this->multiple = $multiple;
		if (count($this->options) < 1) {
			throw new Exception('Tenminste 1 optie nodig voor selectieveld: ' . $name);
		}
	}

	public function validate() {
		if (!array_key_exists($this->value, $this->options)) {
			if ($this->value !== null) {
				$this->error = 'Onbekende optie gekozen';
			}
			if ($this->size === 1 && !parent::validate()) {
				return false;
			}
		}
		return $this->error === '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<select';
		if ($this->multiple) {
			echo ' multiple';
		}
		if ($this->size > 1) {
			echo ' size="' . $this->size . '"';
		}
		echo $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'onchange', 'onclick')) . '>';

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

	public $notnull = true;

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
		require_once 'verticale.class.php';
		$verticalen = Verticale::getNamen();
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
			echo '<input type="radio" id="field_' . $this->getName() . '_option_' . $value . '" value="' . $value . '"' . $this->getInputAttribute(array('name', 'origvalue', 'class', 'disabled', 'onchange', 'onclick'));
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
			return $this->getJaar() . '-' . $this->getMaand() . '-' . $this->getDag();
		} else {
			return parent::getValue();
		}
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

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		$years = range($this->minyear, $this->maxyear);
		$mounths = range(1, 12);
		$days = range(1, 31);

		//als de datum al nul is, moet ie dat ook weer kunnen worden...
		if ($this->value == '0000-00-00' OR $this->value == 0) {
			$years[] = '0000';
			$mounths[] = 0;
			$days[] = 0;
		}

		echo '<select id="field_' . $this->name . '_dag" name="' . $this->name . '_dag" origvalue="' . substr($this->origvalue, 8, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($days as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 8, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_maand" name="' . $this->name . '_maand" origvalue="' . substr($this->origvalue, 5, 2) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($mounths as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 5, 2)) {
				echo ' selected="selected"';
			}

			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_jaar" name="' . $this->name . '_jaar" origvalue="' . substr($this->origvalue, 0, 4) . '" ' . $this->getInputAttribute('class') . '>';
		foreach ($years as $value) {
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 4)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select>';
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

	/**
	 * Speciaal geval:
	 * Niet gepost = uitgevinkt.
	 * 
	 * @return boolean
	 */
	public function getValue() {
		if ($this->isPosted()) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if (!$this->value AND $this->notnull) {
			if ($this->leden_mod AND LoginLid::mag('P_LEDEN_MOD')) {
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

		echo '<input type="checkbox"' . $this->getInputAttribute(array('id', 'name', 'value', 'origvalue', 'class', 'disabled', 'onchange', 'onclick'));
		if ($this->value) {
			echo ' checked="checked" ';
		}
		echo '/>';

		echo '</div>';
	}

}

class RequiredVinkField extends VinkField {

	public $notnull = true;

}

/**
 * Submit, reset en cancel buttons
 */
class SubmitResetCancel implements FormElement {

	public $submitTitle = 'Invoer opslaan';
	public $submitText;
	public $submitIcon;
	public $resetTitle = 'Reset naar opgeslagen gegevens';
	public $resetText;
	public $resetIcon;
	public $cancelTitle = 'Niet opslaan en terugkeren';
	public $cancelText;
	public $cancelIcon;
	public $cancelUrl;
	public $cancelReset;
	public $extraTitle;
	public $extraText;
	public $extraIcon;
	public $extraUrl;
	public $extraActie;
	public $js;

	public function __construct($cancel_url = '', $icons = true, $text = true, $reset = true) {
		$this->cancelUrl = $cancel_url;
		$this->js = '';
		if ($icons) {
			$this->submitIcon = 'disk';
			$this->resetIcon = 'arrow_rotate_anticlockwise';
			$this->cancelIcon = 'delete';
		}
		if ($text) {
			$this->submitText = 'Opslaan';
			$this->resetText = 'Reset';
			$this->cancelText = 'Annuleren';
		}
		if (!$reset) {
			unset($this->resetIcon);
			unset($this->resetText);
		}
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function view() {
		echo '<div class="FormButtons">';
		if (isset($this->extraIcon) OR isset($this->extraText)) {
			if (isset($this->extraIcon)) {
				$this->extraIcon = '<img src="' . CSR_PICS . '/famfamfam/' . $this->extraIcon . '.png" class="icon" width="16" height="16" alt="extra" /> ';
			}
			echo '<a id="extraButton" class="knop';
			if (isset($this->extraActie)) {
				echo ' ' . $this->extraActie;
			}
			echo '" title="' . $this->extraTitle . '" href="' . $this->extraUrl . '">' . $this->extraIcon . $this->extraText . '</a> ';
		}
		if (isset($this->submitIcon) OR isset($this->submitText)) {
			if (isset($this->submitIcon)) {
				$this->submitIcon = '<img src="' . CSR_PICS . '/famfamfam/' . $this->submitIcon . '.png" class="icon" width="16" height="16" alt="submit" /> ';
			}
			echo '<a class="knop submit" title="' . $this->submitTitle . '">' . $this->submitIcon . $this->submitText . '</a> ';
		}
		if (isset($this->resetIcon) OR isset($this->resetText)) {
			if (isset($this->resetIcon)) {
				$this->resetIcon = '<img src="' . CSR_PICS . '/famfamfam/' . $this->resetIcon . '.png" class="icon" width="16" height="16" alt="reset" /> ';
			}
			echo '<a class="knop reset" title="' . $this->resetTitle . '">' . $this->resetIcon . $this->resetText . '</a> ';
		}
		if (isset($this->cancelIcon) OR isset($this->cancelText)) {
			if (isset($this->cancelIcon)) {
				$this->cancelIcon = '<img src="' . CSR_PICS . '/famfamfam/' . $this->cancelIcon . '.png" class="icon" width="16" height="16" alt="cancel" /> ';
			}
			echo '<a class="knop' . ($this->cancelReset ? ' reset' : '') . ' cancel" title="' . $this->cancelTitle . '"' . ($this->cancelUrl !== '' ? ' href="' . $this->cancelUrl . '"' : '') . '>' . $this->cancelIcon . $this->cancelText . '</a>';
		}
		echo '</div>';
	}

	public function getJavascript() {
		return $this->js;
	}

}

/**
 * Commentaardingen voor formulieren
 */
class HtmlComment implements FormElement {

	protected $comment;

	public function __construct($comment) {
		$this->comment = $comment;
	}

	public function getModel() {
		return $this->comment;
	}

	public function view() {
		echo $this->comment;
	}

	public function getJavascript() {
		return '';
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

}

class UbbComment extends HtmlComment {

	public function view() {
		echo CsrUbb::parse($this->comment);
	}

}

class Subkopje extends HtmlComment {

	public function view() {
		echo '<h3>' . $this->comment . '</h3>';
	}

}
