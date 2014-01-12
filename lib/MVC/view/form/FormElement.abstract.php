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
 *  - FormField						Elementen die data leveren.
 *  - SubmitButton					Submitten van het formulier.
 *  - HTMLComment					Uitleg/commentaar in een formulier stoppen.
 *
 * Uitbreidingen van FormField:
 * 	- TextareaField					Textarea
 * 		* PreviewTextField			Textarea met ubb voorbeeld 
 * 		* AutoresizeTextareaField	Textarea die automagisch uitbreidt bij typen, lijkt een autoresizing input
 * 	- InputField					Simpele input
 * 		* CountryField				Landjes 
 * 		* UidField					Uid's  met preview
 * 		* StudieField
 * 		* EmailField
 * 		* UrlField					Urls
 * 	- LidField						Leden selecteren
 * 	- IntField						Integers 
 * 		* NickField					Nicknames
 * 		* TelefoonField
 * 	- FloatField					Bedragen
 * 	- PassField						Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 * 	- SelectField
 * 		* GeslachtField
 * 		* JaNeeField
 * 		* VerticaleField			Verticalen
 * 		* KerkField
 * 	- DatumField					Datums (want data is zo ambigu)
 *
 * SubmitButton
 *
 * Uitbreidingen van HTMLComment:
 * 		- HTMLComment				invoer wordt als html weergegeven.
 * 		- UBBComment				invoer wordt als ubb geparsed
 * 		- Comment					invoer wordt niet geparsed en in een <h3> weergegeven.
 *
 * Voorbeeld:
 *
 * $form=new Formulier(
 * 		'formulier-ID',
 * 		'/index.php',
 * 		array(
 * 			InputField('naam', '', 'Naam'),
 * 			PassField('password'),
 * 			SubmitButton('save')
 * 		);
 * 
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement
 */
abstract class FormElement implements View {

	public function getModel() {
		return $this;
	}

	public function getType() {
		return get_class($this);
	}

	abstract public function getJavascript();
}

/**
 * class FormField is de moeder van input die data leveren.
 */
class InputField extends FormElement implements Validator {

	public $name;  //naam van het veld in POST
	public $value;  //welke initiele waarde heeft het veld?
	public $origvalue; //welke originele waarde had het veld?
	public $title;  //omschrijving bij mouseover title
	public $disabled = false;   //veld uitgeschakeld?
	public $notnull = false; //mag het veld leeg zijn?
	public $forcenotnull = false;  //mag het veld echt niet leeg zijn? (ook voor LEDEN_MOD)
	public $autocomplete = true;   //browser laten autoaanvullen?
	public $placeholder = null;  //plaats een grijze placeholdertekst in leeg veld
	public $error = ''; //foutmelding van dit veld
	public $onchange = null;   //javascript onChange
	public $onclick = null;   //javascript onClick
	public $max_len = 0; //maximale lengte van de invoer.
	public $rows = 0;  //aantal rijen van textarea
	//array met classnames die later in de class-tag komen.
	public $inputClasses = array('regular');
	//array met suggesties die de javascript-autocomplete aan gaat bieden.
	public $suggestions = array();
	public $remotedatasource = '';

	public function __construct($name, $value, $description = null) {
		$this->name = $name;
		$this->value = $value;
		$this->origvalue = $value;
		$this->description = $description;

		if ($this->isPosted() !== false) {
			$this->value = $this->getValue();
		}

		//add *Field classname to cssclasses
		$this->inputClasses[] = $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function getName() {
		return $this->name;
	}

	public function isPosted() {
		return isset($_POST[$this->name]);
	}

	//een remotedatasource instellen overruled suggestions
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
			return trim($_POST[$this->name]);
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
		//vallen over lege velden als dat aangezet is voor het veld en als gebruiker geen LEDEN_MOD heeft of het geforceerd wordt.
		if (!$this->isPosted()) {
			$this->error = 'Veld is niet gepost';
		} elseif ($this->getValue() === '' AND ($this->forcenotnull OR ($this->notnull AND !LoginLid::instance()->hasPermission('P_LEDEN_MOD')))) {
			$this->error = 'Dit is een verplicht veld';
		}
		//als max_len > 0 dan checken of de lengte er niet overheen gaat.
		if ($this->max_len > 0 AND strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Dit veld mag maximaal ' . $this->max_len . ' tekens lang zijn';
		}
		return $this->error == '';
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv() {
		$cssclass = 'veld';
		if ($this->error != '') {
			$cssclass.=' metfouten';
		}
		return '<div class="' . $cssclass . '" id="' . $this->name . '" ' . $this->getInputAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	protected function getLabel() {
		if ($this->description != null) {
			echo '<label for="field_' . $this->name . '">' . mb_htmlentities($this->description) . '</label>';
		}
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
	 * Geef een foutmelding voor dit veld terug.
	 */
	public function getError($html = true) {
		if ($html === false) {
			return $this->error;
		}

		if ($this->error != '') {
			return '<div class="waarschuwing">' . $this->error . '</div>';
		}
	}

	/**
	 * De input kan allerlei CSS-classes hebben. Geef hier een lijstje
	 * terug...
	 */
	protected function getInputClasses() {
		if ($this->remotedatasource != '') {
			$this->inputClasses[] = 'hasRemoteSuggestions';
		} elseif (count($this->suggestions) > 0) {
			$this->inputClasses[] = 'hasSuggestions';
		}
		return $this->inputClasses;
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
				break;
			case 'class': return 'class="' . implode(' ', $this->getInputClasses()) . '"';
				break;
			case 'value': return 'value="' . htmlspecialchars($this->value) . '"';
				break;
			case 'origvalue': return 'origvalue="' . htmlspecialchars($this->origvalue) . '"';
				break;
			case 'name': return 'name="' . $this->name . '"';
				break;
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
		echo $this->getError();

		echo '<input type="text"' . $this->getInputAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . ' />';

		echo $this->getFieldSuggestions();
		//afsluiten van de div om de hele tag heen.
		echo '</div>';
	}

	/**
	 * Javascript nodig voor dit *Field. Dit wordt één keer per *Field
	 * geprint door het Formulier-object.
	 */

	/**
	 * Toelichting op options voor RemoteSuggestions
	 * result = array(
	 * 		array(data:array(..,..,..), value: "string", result:"string"),
	 * 		array(... )
	 * )
	 * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
	 */
	public function getJavascript() {
		return <<<JS
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

/*
 * een TextField levert een textarea.
 */

class TextareaField extends InputField {

	public function __construct($name, $value, $description = null, $rows = 5, $max_len = 0) {
		parent::__construct($name, $value, $description);
		$this->rows = (int) $rows;
		$this->max_len = (int) $max_len;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

		echo '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'rows', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . '>';
		echo htmlspecialchars($this->value);
		echo '</textarea>';

		echo $this->getFieldSuggestions();
		echo '</div>';
	}

}

/*
 * een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */

class AutoresizeTextareaField extends TextareaField {

	public function __construct($name, $value, $description = null, $max_len = 255, $placeholder = null) {
		parent::__construct($name, $value, $description, 1, $max_len);
		$this->inputClasses[] = 'wantsAutoresize';
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
			'width': textarea.css('width')});
	$('body').append(hiddenDiv);  
  
	textarea.bind('keyup', function() {  
  
		content = textarea.val();  
		content = content.replace(/\\n/g, '<br>');  
		hiddenDiv.html(content+'<br>');  
  
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
class PreviewTextField extends TextareaField {

	private $previewOnEnter = false;

	public function __construct($name, $value, $description = null) {
		parent::__construct($name, $value, $description);
		$this->inputClasses[] = 'wantsPreview';
	}

	/**
	 * Bij elk keyup-event een nieuwe preview maken.
	 * Genereert dus heel veel evens, niet erg wenselijk, moet dus nog
	 * een time-out in komen...
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
	}
	
	textarea.wrap('<div class="UBBpreview regular"  style="width: '+(textarea.width()+6)+'px" />')
			.before('<div id="preview_'+fieldname+'" class="preview" style="display: none;"></div>')
			.after($('<a style="float: left; margin-left: 0px;" class="knop">voorbeeld</a>').click(triggerPreview))
			.after($('<a style="float: right;" class="knop" title="Opmaakhulp weergeven" onclick="toggleDiv(\'ubbhulpverhaal\')">UBB</a>'))
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

class RequiredPreviewTextField extends PreviewTextField {

	public $notnull = true;

}

/**
 * Een InputField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class SuggestionField extends InputField {

	public $max_len = 255;

	public function __construct($name, $value, $description, $max_len = 255, $suggestions = array()) {
		parent::__construct($name, htmlspecialchars_decode($value), $description);
		$this->max_len = (int) $max_len;
		parent::setSuggestions($suggestions);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() == '') {
			return true;
		}

		if (mb_strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Maximaal ' . $this->max_len . ' karakters toegestaan.';
		}
		return $this->error == '';
	}

	public function getValue() {
		return htmlspecialchars(parent::getValue());
	}

}

class RequiredInputField extends SuggestionField {

	public $notnull = true;

}

/**
 * CountryField met een aantal autocomplete suggesties voor landen.
 * Doet verder geen controle op niet-bestaande landen...
 */
class CountryField extends InputField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);

		$landSuggesties = array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
		$this->setSuggestions($landSuggesties);
	}

}

class RequiredCountryField extends CountryField {

	public $notnull = true;

}

/**
 * In een UidField kunnen we een uid invullen.
 * Erachter zal dan de naam komen te staan. Het veld valideert als
 *  - het leeg is.
 *  - het een geldig uid bevat.
 */
class UidField extends SuggestionField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 4);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}

		//leeg veld wel accepteren.
		if ($this->getValue() == '') {
			return true;
		}

		//maar als er iets wordt ingevuld, moet het wel een uid zijn.
		if (!Lid::exists($this->getValue())) {
			$this->error = 'Geen geldig uid opgegeven.';
		}
		return $this->error == '';
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
class LidField extends InputField {

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
		$this->inputClasses[] = 'wantsLidPreview';
	}

	/**
	 * LidField::getValue() levert altijd een uid of '' op.
	 */
	public function getValue() {
		//leeg veld meteen teruggeven
		if ($this->getOriginalValue() == '') {
			return '';
		}
		//uid opzoeken
		if ($uid = namen2uid($this->getOriginalValue(), $this->zoekin) AND isset($uid[0]['uid'])) {
			return $uid[0]['uid'];
		}
		return '';
	}

	/**
	 * getOriginalValue() levert het ingevoerde.
	 */
	public function getOriginalValue() {
		return parent::getValue();
	}

	/**
	 * checkt of er een uniek lid wordt gevonden
	 */
	public function validate() {
		if (!parent::validate()) {
			return false;
		}

		//leeg veld wel accepteren.
		if ($this->getOriginalValue() == '') {
			return true;
		}

		$uid = namen2uid($this->getOriginalValue(), $this->zoekin);
		if ($uid) {
			if (isset($uid[0]['uid']) AND Lid::exists($uid[0]['uid'])) {
				return true;
			} elseif (count($uid[0]['naamOpties']) > 0) { //meerdere naamopties?
				$this->error = 'Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error = 'Geen geldig lid';
		return $this->error == '';
	}

	/**
	 * Voeg een preview-div toe achter het veld, defenier een
	 * keyup-event op het veld, en trigger het event meteen om de boel
	 * meteen te vullen.
	 */
	public function getJavascript() {
		$zoekin = '';
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

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() == '') {
			$this->error = 'Dit is een verplicht veld.';
		}
		return $this->error == '';
	}

}

/**
 * StudieField
 *
 * Suggereert een aantal studies, doet verder geen controle op invoer.
 */
class StudieField extends SuggestionField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 100);

		//de studies aan de TU, even prefixen met 'TU Delft - '
		$tustudies = array('BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB');
		$tustudies = array_map(create_function('$value', 'return "TU Delft - ".$value;'), $tustudies);

		$andere = array('INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden');

		$this->setSuggestions(array_merge($tustudies, $andere));
	}

}

class EmailField extends InputField {

	/**
	 * Dikke valideerfunctie voor emails.
	 */
	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() == '') {
			return true;
		}
		//bevat het email-adres een @
		if (strpos($this->getValue(), '@') === false) {
			$this->error = 'Ongeldig formaat email-adres';
		} else {
			# anders gaan we m ontleden en controleren
			list ($usr, $dom) = explode('@', $this->getValue());
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
		return $this->error == '';
	}

}

class RequiredEmailField extends EmailField {

	public $notnull = true;

}

/**
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends InputField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() == '') {
			return true;
		}

		// controleren of het een geldige url is...
		if (!is_utf8($this->getValue()) OR !preg_match('#([\w]+?://[^ "\n\r\t<]*?)#is', $this->getValue())) {
			$this->error = 'Ongeldige karakters:';
		} elseif ($this->max_len != null && mb_strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Gebruik maximaal ' . $this->max_len . ' karakters:';
		}
		return $this->error == '';
	}

}

/**
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends InputField {

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
		if (!parent::validate()) { // als dit een veld verplicht is heeft het in FormField::validate() al een foutmelding opgeleverd.
			return false;
		}

		if (!$this->notnull AND parent::getValue() === '') {
			// do not check if empty
		} else if (!preg_match('/\d+/', parent::getValue())) {
			$this->error = 'Alleen getallen toegestaan';
		} else if ($this->max !== null AND $this->getValue() > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} else if ($this->min !== null AND $this->getValue() < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error == '';
	}

}

/**
 * Invoeren van een float. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class FloatField extends InputField {

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
		if (!parent::validate()) { // als dit een veld verplicht is heeft het in FormField::validate() al een foutmelding opgeleverd.
			return false;
		}

		if (!$this->notnull AND strlen(parent::getValue()) === 0) {
			// do not check if empty
		} else if (!preg_match('/\d+(,{1}\d*)?/', str_replace('.', ',', parent::getValue()))) {
			$this->error = 'Alleen komma-getallen toegestaan';
		} else if ($this->max !== null AND $this->getValue() > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} else if ($this->min !== null AND $this->getValue() < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error == '';
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
 * NickField
 *
 * is pas valid als dit lid de enige is met deze nick.
 */
class NickField extends InputField {

	public $max_len = 20;

	public function validate($lid = null) {
		if (!parent::validate()) {
			return false;
		}

		//lege nicknames vinden we prima.
		if ($this->getValue() == '') {
			return true;
		}

		if (!is_utf8($this->getValue())) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst.';
		} elseif (mb_strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Gebruik maximaal ' . $this->max_len . ' karakters.';
			# 2e check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
			# omdat this->nickExists in mysql case-insensitive zoek
		} elseif (strtolower($lid->getNickname()) != strtolower($this->getValue()) AND Lid::nickExists($this->getValue())) {
			$this->error = 'Deze bijnaam is al in gebruik.';
		}
		return $this->error == '';
	}

}

/**
 * TelefoonField
 *
 * is valid als er een enigszins op een telefoonnummer lijkende string wordt
 * ingegeven.
 */
class TelefoonField extends SuggestionField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() == '') {
			return true;
		}
		if (!preg_match('/^([\d\+\-]{10,20})$/', $this->getValue())) {
			$this->error = 'Geen geldig telefoonnummer.';
		}

		return $this->error == '';
	}

}

/**
 * PassField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 */
class PassField extends InputField {

	public function __construct($name) {
		$this->name = $name;
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

	public function validate($lid = null) {
		if (!$lid instanceof Lid) {
			throw new Exception($this->getType() . '::validate() moet een Lid-object meekrijgen');
		}
		if (!parent::validate()) {
			return false;
		}
		$current = $_POST[$this->name . '_current'];
		$new = $_POST[$this->name . '_new'];
		$confirm = $_POST[$this->name . '_confirm'];
		if ($current != '') {
			if (!$lid->checkpw($current)) {
				$this->error = 'Uw huidige wachtwoord is niet juist';
			} else {
				if ($new == '' OR $confirm == '') {
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
		if ($new != '' AND $current == '') {
			$this->error = 'U dient uw huidige wachtwoord ook in te voeren';
		}
		return $this->error == '';
	}

	public function view() {
		echo $this->getDiv();
		echo '<div class="password">';
		echo $this->getError();
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
	public $cssOptions;
	public $size;
	public $multiple; //TODO

	public function __construct($name, $value, $description, array $options, $cssOptions = array(), $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->cssOptions = $cssOptions;
		$this->size = (int) $size;
		$this->multiple = $multiple;
		if (count($this->options) < 1) {
			throw new Exception('Tenminste 1 optie nodig voor selectieveld: ' . $name);
		}
		$this->notnull = true;
	}

	public function validate() {
		if (!array_key_exists($this->getValue(), $this->options)) {
			if ($this->getValue() !== null) {
				$this->error = 'Onbekende optie gekozen';
			}
			if ($this->size === 1 && !parent::isValid()) {
				return false;
			}
		}
		return $this->error == '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

		echo '<select origvalue="' . $this->origvalue . '" ';
		if ($this->multiple) {
			echo 'multiple ';
		}
		if ($this->size > 1) {
			echo 'size="' . $this->size . '"';
		}
		echo $this->getInputAttribute(array('id', 'name', 'class', 'disabled', 'onchange', 'onclick')) . '>';

		foreach ($this->options as $value => $description) {
			echo '<option value="' . $value . '"';
			if ($value == $this->value) {
				echo ' selected="selected"';
			}
			if (array_key_exists($value, $this->cssOptions)) {
				echo ' class="' . $this->cssOptions[$value] . '"';
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
		echo $this->getError();

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
		if (!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $this->getValue())) {
			$this->error = 'Ongeldige datum';
		} elseif (substr($this->getValue(), 0, 4) > $this->maxyear) {
			$this->error = 'Er kunnen geen data later dan ' . $this->maxyear . ' worden weergegeven';
		} elseif ($this->getValue() != '0000-00-00' AND !checkdate($this->getMaand(), $this->getDag(), $this->getJaar())) {
			$this->error = 'Datum bestaat niet';
		}
		return $this->error == '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

		$years = range($this->minyear, $this->maxyear);
		$mounths = range(1, 12);
		$days = range(1, 31);

		//als de datum al nul is, moet ie dat ook weer kunnen worden...
		if ($this->getValue() == '0000-00-00' OR $this->getValue() == 0) {
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
		if (!preg_match('/^(\d\d?):(\d{2})$/', $this->getValue())) {
			$this->error = 'Ongeldige tijdstip';
		} elseif (substr($this->getValue(), 0, 2) > 23 OR substr($this->getValue(), 3, 5) > 59) {
			$this->error = 'Tijdstip bestaat niet';
		}
		return $this->error == '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

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
		if (parent::isPosted()) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if ($this->notnull AND $this->getValue() == false) {
			$this->error = 'Dit is een verplicht veld.';
		}
		return $this->error == '';
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

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
 * SubmitButton.
 */
class SubmitButton extends FormElement {

	protected $buttontext;
	protected $extra = '';

	public function __construct($buttontext = 'opslaan', $extra = '') {
		$this->buttontext = $buttontext;
		$this->extra = $extra;
	}

	public function view() {
		echo <<<HTML
<div class="submit">
	<label for="submit"> </label>
	<input type="submit" value="{$this->buttontext}" />
	<input type="reset" value="reset formulier" />
	{$this->extra}
</div>
HTML;
	}

	public function getJavascript() {
		
	}

}

/**
 * Commentaardingen voor formulieren
 */
class HTMLComment extends FormElement {

	public $comment;

	public function __construct($comment) {
		$this->comment = $comment;
	}

	public function view() {
		echo $this->comment;
	}

	public function getJavascript() {
		
	}

}

class UBBComment extends HTMLComment {

	public function view() {
		echo CsrUBB::instance()->getHtml($this->comment);
	}

}

class Comment extends HTMLComment {

	public function view() {
		echo '<h3>' . $this->comment . '</h3>';
	}

}
