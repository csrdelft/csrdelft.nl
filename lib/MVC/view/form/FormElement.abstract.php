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
abstract class FormElement implements View {

	protected $model;

	public function __construct($model) {
		$this->model = $model;
	}

	public function getModel() {
		return $this->model;
	}

	public function getType() {
		return get_class($this);
	}

	public function getJavascript() {
		return '';
	}

}

/**
 * InputField is de moeder van alle FormElements die data leveren.
 */
abstract class InputField extends FormElement implements Validator {

	public $name;  //naam van het veld in POST
	public $value;  //welke initiele waarde heeft het veld?
	public $origvalue; //welke originele waarde had het veld?
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
	public $max_len = 0; //maximale lengte van de invoer.
	public $rows = 0;  //aantal rijen van textarea
	public $css_classes = array('FormField'); //array met classnames die later in de class-tag komen.
	public $suggestions = array(); //array met suggesties die de javascript-autocomplete aan gaat bieden.
	public $remotedatasource = '';

	public function __construct($name, $value, $description = null, $model = null) {
		parent::__construct($model);

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

	public function getName() {
		return $this->name;
	}

	public function isPosted() {
		return array_key_exists($this->name, $_POST);
	}

	/**
	 * Een remotedatasource overruled suggestions
	 */
	public function setRemoteSuggestionsSource($url) {
		$this->remotedatasource = $url;
	}

	public function setSuggestions($array) {
		$this->suggestions = $array;
	}

	public function setPlaceholder($bericht) {
		$this->placeholder = $bericht;
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
			if ($this->leden_mod AND LoginLid::instance()->hasPermission('P_LEDEN_MOD')) {
				
			} else {
				$this->error = 'Dit is een verplicht veld';
			}
		}
		//als max_len > 0 dan checken of de lengte er niet overheen gaat.
		if ($this->max_len > 0 AND strlen($this->value) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		return $this->error === '';
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv() {
		$cssclass = 'InputField';
		if ($this->error != '') {
			$cssclass.=' metFouten';
		}
		return '<div class="' . $cssclass . '" id="' . $this->name . '" ' . $this->getInputAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	protected function getLabel() {
		if ($this->description != null) {
			return '<label for="field_' . $this->name . '">' . mb_htmlentities($this->description) . '</label>';
		}
		return '';
	}

	/**
	 * Zorg dat de suggesties gegeven gaan worden.
	 */
	protected function getFieldSuggestions() {
		$return = '';

		if (count($this->suggestions) > 0 OR $this->remotedatasource != '') {
			if ($this->remotedatasource != '') {
				$suggestions = $this->remotedatasource;
			} else {
				$suggestions = $this->suggestions;
			}
			$return .= '<script language="javascript"> ' . "\n";
			$return .= 'FieldSuggestions["' . $this->name . '"]=' . json_encode($suggestions) . "; \n";
			$return .= '</script>';
		}
		return $return;
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
		return '<div class="waarschuwing">' . $this->getError() . '</div>';
	}

	/**
	 * Geef lijst van allerlei CSS-classes voor dit veld terug.
	 */
	protected function getCssClasses() {
		if ($this->notnull) {
			if ($this->leden_mod AND LoginLid::instance()->hasPermission('P_LEDEN_MOD')) {
				
			} else {
				$this->css_classes[] = 'required';
			}
		}
		if ($this->remotedatasource != '') {
			$this->css_classes[] = 'hasRemoteSuggestions';
		} elseif (count($this->suggestions) > 0) {
			$this->css_classes[] = 'hasSuggestions';
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
				$return.=' ' . $this->getInputAttribute($a);
			}
			return $return;
		}
		switch ($attr) {
			case 'id': return 'id="field_' . $this->getName() . '"';
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
				if (!$this->autocomplete OR count($this->suggestions) > 0 OR $this->remotedatasource != '') {
					return 'autocomplete="off"';
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

		echo $this->getFieldSuggestions();
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
		return <<<JS
function field_change(event, field){
	if(event) {
		field = $(this);
	}
	$(field).parent().removeClass('metFouten');
	$(field).parent().find('div.waarschuwing').html('');
}
$('.metFouten .FormField').change(field_change);
$('.required').focusout(function(event){
	console.log($(this).val().length);
	if ($(this).val().length < 1) {
		$(this).parent().addClass('metFouten');
		$(this).parent().find('div.waarschuwing').html('Dit is een verplicht veld');
	}
	else {
		field_change(false, $(this));
	}
});
$('.hasSuggestions').each(function(index, tag){
	$('#'+tag.id).autocomplete(
		FieldSuggestions[tag.id.substring(6)],
		{clickFire: true, max: 20, matchContains: true }
	);
});
$('.hasRemoteSuggestions').each(function(index, tag){
	$('#'+tag.id).autocomplete(
		FieldSuggestions[tag.id.substring(6)], {
			dataType: 'json',
			parse: function(result) { return result; },
			formatItem: function(row, i, n) { return row[0]; },
			clickFire: true, 
			max: 20
		}
	).result(function(){ $(this).keyup(); });
});
JS;
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

	public function __construct($name, $value, $description, $max_len = 255, $model = null) {
		parent::__construct($name, $value, $description, $model);
		$this->max_len = (int) $max_len;
		$this->value = htmlspecialchars_decode($this->value);
		$this->origvalue = htmlspecialchars_decode($value);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!is_utf8($this->value)) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst.';
		} elseif ($this->max_len > 0 AND mb_strlen($this->value) > $this->max_len) {
			//als max_len > 0 dan checken of de lengte er niet overheen gaat.
			$this->error = 'Maximaal ' . $this->max_len . ' karakters toegestaan.';
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
		parent::__construct($name, $value, $description, 4);
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

	/**
	 * Voeg een preview-div toe achter het veld, defenier een keyup-
	 * event op het veld waarin de ajax-request gedaan wordt en trigger
	 * het event meteen om de boel meteen te vullen.
	 */
	public function getJavascript() {
		return <<<JS
$('.UidField').each(function(index, tag){
	$(this).after('<div id="uidPreview_'+$(this).attr('id').substring(6)+'" class="uidPreview" />');
	$(this).keyup(function(){
		var field=$(this);
		if(field.val().length==4){
			$.ajax({
				url: "/tools/naamlink.php?uid="+field.val(),
				success: function(response){
					$('#uidPreview_'+field.attr('id').substring(6)).html(response);
					init_visitekaartjes();
				}
			});
		}
	}).keyup();
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
		$lid = LidCache::getLid($value);
		if ($lid instanceof Lid) {
			$value = $lid->getNaamLink('full', 'plain');
		}
		parent::__construct($name, $value, $description);
		if (!in_array($zoekin, array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies'))) {
			$zoekin = 'leden';
		}
		$this->zoekin = $zoekin;
		$this->setRemoteSuggestionsSource('/tools/naamsuggesties/' . $this->zoekin);
		$this->css_classes[] = 'wantsLidPreview';
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

	/**
	 * Voeg een preview-div toe achter het veld, defenier een
	 * keyup-event op het veld, en trigger het event meteen om de boel
	 * meteen te vullen.
	 */
	public function getJavascript() {
		$js = parent::getJavascript();
		$js.=<<<JS
$('.wantsLidPreview').each(function(index, tag){
	var suggesties=FieldSuggestions[$(this).attr('id').substring(6)].split("/");
	$(this).after('<div id="lidPreview_'+$(this).attr('id').substring(6)+'" class="lidPreview" />');
	$(this).keyup(function(){
		var field=$(this);
		if(field.val().length>2){
			$.ajax({
				url: "/tools/naamlink.php?naam="+field.val()+ "&"+$.param({ zoekin: suggesties[suggesties.length-1] }),
				success: function(response){
					$('#lidPreview_'+field.attr('id').substring(6)).html(response);
					init_visitekaartjes();
				}
			});
		}else{
			$('#lidPreview_'+field.attr('id').substring(6)).html('');
		}
	}).keyup();
});
JS;
		return $js;
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
			} elseif (!checkdnsrr($dom, 'A') and !checkdnsrr($dom, 'MX')) {
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
		if (!is_utf8($this->value) OR !preg_match('#([\w]+?://[^ "\n\r\t<]*?)#is', $this->value)) {
			$this->error = 'Ongeldige karakters:';
		} elseif ($this->max_len != null && mb_strlen($this->value) > $this->max_len) {
			$this->error = 'Gebruik maximaal ' . $this->max_len . ' karakters:';
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

	public function __construct($name, $value, $description, $max = null, $min = null, $empty = false) {
		parent::__construct($name, $value, $description);

		if ($max !== null) {
			$this->max = (int) $max;
		}
		if ($min !== null) {
			$this->min = (int) $min;
		}
		$this->notnull = !$empty;
	}

	public function getValue() {
		return (int) parent::getValue();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		//parent checks notnull
		if (parent::getValue() === '') {
			return true;
		} else if (!preg_match('/\d+/', parent::getValue())) {
			$this->error = 'Alleen getallen toegestaan';
		} else if ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} else if ($this->min !== null AND $this->value < $this->min) {
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

	public function __construct($name, $value, $description, $max = null, $min = null, $empty = false) {
		parent::__construct($name, $value, $description);
		if ($max !== null) {
			$this->max = (float) $max;
		}
		if ($min !== null) {
			$this->min = (float) $min;
		}
		$this->notnull = !$empty;
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
		} else if (!preg_match('/\d+(,{1}\d*)?/', str_replace('.', ',', parent::getValue()))) {
			$this->error = 'Alleen komma-getallen toegestaan';
		} else if ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} else if ($this->min !== null AND $this->value < $this->min) {
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
		parent::__construct($name, $value, $description, 255, $lid);
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

	public function __construct($name, $value, $description = null, $rows = 5, $max_len = 0) {
		parent::__construct($name, $value, $description, $max_len);
		$this->rows = (int) $rows;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'rows', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . '>';
		echo $this->value;
		echo '</textarea>';

		echo $this->getFieldSuggestions();
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

	public function __construct($name, $value, $description = null, $max_len = 255, $placeholder = null) {
		parent::__construct($name, $value, $description, 1, $max_len);
		$this->css_classes[] = 'wantsAutoresize';
		$this->placeholder = $placeholder;
	}

	public function getJavascript() {
		// maakt een verborgen div met dezelfde eigenschappen als de textarea
		// en gebruikt autoresize eigenschappen van de div om de hoogte te bepalen voor de textarea
		return <<<JS
$('.wantsAutoresize').each(function(){
	var textarea=$(this);
	var fieldname=textarea.attr('id').substring(6);
	var hiddenDiv = $(document.createElement('div')),
	content = null;

	hiddenDiv.addClass('hiddendiv '+fieldname)
		.css({'font-size': textarea.css('font-size'),
			'font-weight': textarea.css('font-size'),
			'width': textarea.css('width'),
			'word-break': 'break-all',
			'visibility': 'hidden'});
	$('body').append(hiddenDiv);

	textarea.bind('keyup', function() {
		content = textarea.val();
		content = content.replace('<', 'X');
		content = content.replace(/\\n/g, '<br>');
		hiddenDiv.html(content+'<br><br>');
		textarea.css('height', hiddenDiv.height());
	}).keyup();
});
JS;
	}

}

class RequiredAutoresizeTextField extends AutoresizeTextareaField {

	public $notnull = true;

}

/**
 * Textarea met een ubb-preview erbij. De hele ubb-preview wordt gemaakt
 * door de javascript in de klasse Formulier, op alle textarea's met
 * de class die hier gegeven wordt.
 *
 * met previewOnEnter() is klikken op het voorbeeld-knopje niet meer
 * nodig, er wordt een voorbeeld gemaakt bij het op enter drukken.
 */
class UbbPreviewField extends TextareaField {

	private $previewOnEnter = false;

	public function __construct($name, $value, $description = null) {
		parent::__construct($name, $value, $description);
		$this->css_classes[] = 'wantsPreview';
	}

	/**
	 * Bij elk keyup-event een nieuwe preview maken.
	 * Genereert dus heel veel evens, niet erg wenselijk, moet dus nog
	 * Een time-out in komen...
	 */
	public function previewOnEnter($value = true) {
		$this->previewOnEnter = $value;
	}

	/**
	 * Wrap het veld in een divje, maak een preview-div aan voor het veld
	 * met dezelde breedte, en voeg een knopje toe om het event te triggeren.
	 */
	public function getJavascript() {
		$js = <<<JS
$('.wantsPreview').each(function(){
	var textarea=$(this);
	var fieldname=textarea.attr('id').substring(6);

	var triggerPreview=function(){
		applyUBB(textarea.val(), document.getElementById('preview_'+fieldname));
		$('#preview_'+fieldname).show();
	};
	var vergrootTextarea=function(){
		var currentRows=parseInt(textarea.attr('rows'));
		textarea.attr('rows', 10 + currentRows);
	};
	
	textarea.wrap('<div class="UBBpreview FormField"  style="width: '+(textarea.width()+6)+'px" />')
			.before('<div id="preview_'+fieldname+'" class="preview" style="display: none;"></div>')
			.after($('<a style="float: left; margin-left: 0px;" class="knop">voorbeeld</a>').click(triggerPreview))
			.after($('<a style="float: right;" class="knop" title="Opmaakhulp weergeven" onclick="$(\'#ubbhulpverhaal\').toggle();">UBB</a>'))
			.after($('<a style="float: right; margin-right: 0px" class="knop" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>').click(vergrootTextarea));

JS;
		//We voegen een keyup-event toe dat bij elke enter een nieuwe
		//preview opvraagt.
		if ($this->previewOnEnter) {
			$js.=<<<JS
	textarea.keyup(
		function(event){
			if(event.keyCode==13){ //enter == 13
				triggerPreview();
			}
		});
JS;
		}
		//en de .each() nog afsluiten
		$js.="});\n";
		return $js;
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
		$this->notnull = true;
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

		echo '<div style="float: left;">';
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
		} elseif ($this->value != '0000-00-00' AND !checkdate($this->getMaand(), $this->getDag(), $this->getJaar())) {
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

	public function getValue() {
		if ($this->isPosted()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Speciaal geval, want niet gepost = uitgevinkt
	 */
	public function validate() {
		if (!$this->value AND $this->notnull) {
			if ($this->leden_mod AND LoginLid::instance()->hasPermission('P_LEDEN_MOD')) {
				
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
class SubmitResetCancel extends FormElement {

	public $submitText = 'Opslaan';
	public $submitTitle;
	public $submitIcon;
	public $resetText = 'Reset';
	public $resetTitle;
	public $resetIcon;
	public $cancelText = 'Annuleren';
	public $cancelTitle;
	public $cancelIcon;
	public $cancelUrl = '';

	/**
	 * Volgorde van parameters naar meest aangepast
	 * @param string $cancelurl
	 * @param string $submittext
	 * @param string $canceltext
	 * @param string $resettext
	 */
	public function __construct($cancel_url = '', $icons = true, $submit_title = 'Invoer opslaan', $cancel_title = 'Niet opslaan en terugkeren', $reset_title = 'Reset naar opgeslagen gegevens') {
		parent::__construct(null);
		$this->submitTitle = $submit_title;
		$this->resetTitle = $reset_title;
		$this->cancelTitle = $cancel_title;
		$this->cancelUrl = $cancel_url;
		if ($icons) {
			$this->submitIcon = '<img src="' . CSR_PICS . 'famfamfam/disk.png" class="icon" width="16" height="16" alt="submit" /> ';
			$this->resetIcon = '<img src="' . CSR_PICS . 'famfamfam/arrow_rotate_anticlockwise.png" class="icon" width="16" height="16" alt="reset" /> ';
			$this->cancelIcon = '<img src="' . CSR_PICS . 'famfamfam/delete.png" class="icon" width="16" height="16" alt="cancel" /> ';
		}
	}

	public function view() {
		echo '<div class="FormButtons">';
		echo '<a class="knop submit" title="' . $this->submitTitle . '">' . $this->submitIcon . $this->submitText . '</a> ';
		echo '<a class="knop reset" title="' . $this->resetTitle . '">' . $this->resetIcon . $this->resetText . '</a> ';
		echo '<a href="' . $this->cancelUrl . '" class="knop cancel" title="' . $this->cancelTitle . '">' . $this->cancelIcon . $this->cancelText . '</a>';
		echo '</div>';
	}

}

/**
 * Commentaardingen voor formulieren
 */
class HtmlComment extends FormElement {

	public $comment;

	public function __construct($comment) {
		parent::__construct(null);
		$this->comment = $comment;
	}

	public function view() {
		echo $this->comment;
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
