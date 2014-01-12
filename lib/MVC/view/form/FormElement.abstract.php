<?php

/**
 * FormElement.abstract.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Dit is een poging om maar op één plek dingen voor een formulier te defenieren:
 *  - validatorfuncties
 *  - Html voor de velden, inclusief bijbehorende javascript.
 *  - suggesties voor formuliervelden
 *
 * Alle elementen die in een formulier terecht kunnen komen stammen af van
 * de class InputField.
 *
 * FormElement
 *  - InputField					Elementen die data leveren.
 *  - SubmitButton					Submitten van het formulier.
 *  - HTMLComment					Uitleg/commentaar in een formulier stoppen.
 *
 * Uitbreidingen van InputField:
 *  	
 * 		- IntField					Integers
 * 		- FloatField				Bedragen
 * 		- UrlField					Urls
 * 		- PassField					Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 * 		- TextareaField				Textarea
 * 			* PreviewTextField		Textarea met ubb voorbeeld 
 * 			* AutoresizeTextField	Textarea die automagisch uitbreidt bij typen, lijkt een autoresizing input
 * 		- SuggestionField			Simpele input
 * 			* CountryField			Landjes 
 * 			* StudieField			Studies
 * 		- TelefoonField				Telefoonnummers
 * 		- EmailField				Emailadres
 * 		- DatumField				Datums
 * 		- UidField					Uid's  met preview
 * 		- LidField					Leden selecteren
 * 		- NickField					Nicknames
 * 		- SelectField
 * 			* GeslachtField
 * 			* JaNeeField
 * 			* VerticaleField		Verticalen
 * 			* KerkField
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
 * 			TextField('naam', '', 'Naam'),
 * 			PassField('password'),
 * 			SubmitButton('save')
 * 		);
 */
abstract class FormElement implements View {

	/**
	 * ID van het HTML element.
	 * @var string
	 */
	public $id;
	/**
	 * Naam van het veld in POST.
	 * @var string
	 */
	public $name;
	/**
	 * De waarde verschilt per element.
	 * @var mixed 
	 */
	public $value;
	/**
	 * Omschrijving bij mouseover
	 * @var string 
	 */
	public $title;
	/**
	 * CSS classes
	 * @var string
	 */
	public $css_classes = array();
	/**
	 * JS onclick handler
	 * @var string
	 */
	public $onclick;
	/**
	 * HTML element uitgeschakeld
	 * @var boolean
	 */
	public $disabled = false;

	public function __construct($id, $value = '', $title = null) {
		$this->id = $id;
		$this->name = $id;
		$this->value = $value;
		$this->title = $title;
		$this->css_classes[] = 'FormElment';
		$this->css_classes[] = $this->getType();
	}

	public function getModel() {
		return $this;
	}

	public function getType() {
		return get_class($this);
	}

	public function isPosted() {
		return array_key_exists($this->name, $_POST) AND isset($_POST[$this->name]);
	}

}

/**
 * InputField is de moeder van alle input fields die data leveren.
 * Standaard required mag deze niet leeg zijn.
 */
class InputField extends FormElement implements Validator {

	/**
	 * Original value of field even after posting.
	 * @var mixed
	 */
	public $orig_value;
	/**
	 * Verplicht veld of mag het leeg blijven.
	 * @var boolean
	 */
	public $required = true;
	/**
	 * Foutmelding.
	 * @var string
	 */
	public $error = null;
	/**
	 * Tekst weergeven bij leeg veld.
	 * @var string
	 */
	public $placeholder = null;
	/**
	 * JS onchange handler.
	 * @var string
	 */
	public $onchange = null;
	/**
	 * Automatisch aanvullen met Ajax.
	 * @var type
	 */
	public $autocomplete = true;
	/**
	 * Suggestielijst voor automatisch aanvullen.
	 * @var array
	 */
	public $suggestions = array();
	/**
	 * Bron van de data overruled suggestios.
	 * @var string
	 */
	public $remotedatasource = null;
	/**
	 * Maximale lengte van de invoer.
	 * @var int
	 */
	public $max_len = 255;

	public function __construct($id, $value, $description = null) {
		parent::__construct($id, $value, $description);
		$this->orig_value = $value;
		$this->description = $description;
		$this->value = $this->getValue();
	}

	public function getValue() {
		if ($this->isPosted()) {
			$value = filter_input(INPUT_POST, $this->name);
			return trim($value);
		}
		return $this->value;
	}

	/**
	 * Elk veld staat in een div en heeft een label met beschrijving.
	 */
	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<input type="text"' . $this->getAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . ' />';

//afsluiten van de div om de hele tag heen.
		echo '</div>';
	}

	/**
	 * Is de invoer voor het veld correct?
	 * 
	 * Kindertjes van deze classe kunnen deze methode overloaden om specifiekere
	 * testen mogelijk te maken.
	 */
	public function validate() {
		$len = strlen($this->getValue());
		if (!$this->isPosted()) {
			$this->error = 'Veld is niet gepost';
		} elseif ($len === 0 AND $this->required) {
			$this->error = 'Dit is een verplicht veld';
		}
		// als max_len > 0 dan checken of de lengte er niet overheen gaat.
		if (is_int($this->max_len) AND $this->max_len > 0 AND $len > $this->max_len) {
			$this->error = 'Maximaal ' . $this->max_len . ' tekens toegestaan';
		}
		return $this->error === null;
	}

	/**
	 * De input kan allerlei CSS-classes hebben.
	 */
	protected function getCssClasses() {
		if ($this->remotedatasource !== null) {
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
	protected function getAttribute($attr) {
		if (is_array($attr)) {
			$return = '';
			foreach ($attr as $a) {
				$return.=' ' . $this->getAttribute($a);
			}
			return $return;
		}
		switch ($attr) {
			case 'id': return 'id="field_' . $this->id . '"';
			case 'class': return 'class="' . implode(' ', $this->getCssClasses()) . '"';
			case 'value': return 'value="' . htmlspecialchars($this->value) . '"';
			case 'origvalue': return 'origvalue="' . htmlspecialchars($this->orig_value) . '"';
			case 'name': return 'name="' . $this->name . '"';
			case 'title':
				if ($this->title !== null) {
					return 'title="' . $this->title . '"';
				}
				break;
			case 'disabled':
				if ($this->disabled) {
					return 'disabled';
				}
				break;
			case 'placeholder':
				if ($this->placeholder !== null) {
					return 'placeholder="' . $this->placeholder . '"';
				}
				break;
			case 'rows':
				if ($this->rows > 0) {
					return 'rows="' . $this->rows . '"';
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
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv() {
		$css_classes = array('FormInput');
		if ($this->error !== null) {
			$css_classes[] = 'metFouten';
		} else {
			$css_classes[] = 'regular';
		}
		return '<div class="' . implode(' ', $css_classes) . '" id="' . $this->id . '" ' . $this->getAttribute('title') . '>';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label.
	 */
	protected function getLabel() {
		if ($this->description !== null) {
			echo '<label for="field_' . $this->id . '">' . mb_htmlentities($this->description) . '</label>';
		}
	}

	/**
	 * Geef de foutmelding voor dit veld terug.
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Geef een div met foutmelding voor dit veld terug.
	 */
	public function getErrorDiv() {
		if ($this->error !== null) {
			return '<div class="waarschuwing">' . $this->error . '</div>';
		}
	}

	public function getJavascript() {
		return '';
	}

}

/**
 * Een TextField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class SuggestionField extends InputField {

	public function __construct($name, $value, $description, $max_len = 255, $suggestions = array()) {
		parent::__construct($name, htmlspecialchars_decode($value), $description);
		$this->max_len = (int) $max_len;
		parent::setSuggestions($suggestions);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->getValue() === '') {
			return true;
		}
		return $this->error === null;
	}

	public function getValue() {
		return htmlspecialchars(parent::getValue());
	}

	/**
	 * Elk veld staat in een div en heeft een label met beschrijving.
	 */
	public function view() {
		parent::view();

		echo $this->getFieldSuggestions();
		echo '</div>'; // afsluiten van de div om de hele tag heen.
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
	 * Javascript nodig voor dit *Field. Dit wordt één keer per *Field
	 * geprint door het Formulier-object.
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
 * Een TextareaField levert een textarea.
 */
class TextareaField extends InputField {

	public $rows = 0;  //aantal rijen van textarea

	public function __construct($name, $value, $description = null, $rows = 5, $max_len = 0) {
		parent::__construct($name, $value, $description);
		$this->rows = (int) $rows;
		$this->max_len = (int) $max_len;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<textarea' . $this->getAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'rows', 'maxlength', 'placeholder', 'autocomplete', 'onchange', 'onclick')) . '>';
		echo htmlspecialchars($this->value);
		echo '</textarea>';

		echo $this->getFieldSuggestions();
		echo '</div>';
	}

}

/*
 * Een textarea die groter wordt als de inhoud niet meer in het veld past.
 */

class AutotesizeTextareaField extends TextareaField {

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
		$this->css_classes[] = 'wantsPreview';
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

/**
 * CountryField met een aantal autocomplete suggesties voor landen.
 * Doet verder geen controle op niet-bestaande landen...
 */
class CountryField extends SuggestionField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);

		$landSuggesties = array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
		$this->setSuggestions($landSuggesties);
	}

}

/**
 * In een UidField kunnen we een uid invullen.
 * Erachter zal dan de naam komen te staan. Het veld valideert als
 *  - het leeg is.
 *  - het een geldig uid bevat.
 */
class UidField extends InputField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description, 4);
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// als er iets wordt ingevuld, moet het een geldig uid zijn.
		if (!Lid::exists($this->getValue())) {
			$this->error = 'Geen geldig uid opgegeven.';
		}
		return $this->error === null;
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
		$this->remotedatasource = '/tools/naamsuggesties/' . $this->zoekin;
		$this->css_classes[] = 'wantsLidPreview';
	}

	/**
	 * LidField::getValue() levert altijd een uid of '' op.
	 */
	public function getValue() {
		if (parent::getValue() === null OR parent::getValue() === '') { // leeg veld meteen teruggeven
			return '';
		}
		if ($uid = namen2uid(parent::getValue(), $this->zoekin) AND isset($uid[0]['uid'])) { // uid opzoeken
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
		return false;
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
		return $this->error === null;
	}

}

/**
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends InputField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// controleren of het een geldige url is...
		if (!is_utf8($this->getValue()) OR !preg_match('#([\w]+?://[^ "\n\r\t<]*?)#is', $this->getValue())) {
			$this->error = 'Ongeldige karakters:';
		} elseif ($this->max_len != null && mb_strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Gebruik maximaal ' . $this->max_len . ' karakters:';
		}
		return $this->error === null;
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
		$this->required = !$empty;
	}

	public function getValue() {
		return (int) parent::getValue();
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->required AND parent::getValue() === '') { // do not check if empty
		} elseif (!preg_match('/\d+/', parent::getValue())) {
			$this->error = 'Alleen getallen toegestaan';
		} elseif ($this->max !== null AND $this->getValue() > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->min !== null AND $this->getValue() < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === null;
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
		$this->required = !$empty;
	}

	public function getValue() {
		return (float) str_replace(',', '.', parent::getValue());
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!$this->required AND strlen(parent::getValue()) === 0) { // do not check if empty
		} elseif (!preg_match('/\d+(,{1}\d*)?/', str_replace('.', ',', parent::getValue()))) {
			$this->error = 'Alleen komma-getallen toegestaan';
		} elseif ($this->max !== null AND $this->getValue() > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->min !== null AND $this->getValue() < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === null;
	}

}

/**
 * Verborgen veld voor de gebruiker.
 */
class HiddenField extends InputField {

	public function view() {
		echo '<input type="hidden"' . $this->getAttribute(array('id', 'name', 'class', 'value', 'origvalue', 'disabled', 'maxlength', 'placeholder', 'autocomplete')) . ' />';
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
		if (!is_utf8($this->getValue())) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst.';
		} elseif (mb_strlen($this->getValue()) > $this->max_len) {
			$this->error = 'Gebruik maximaal ' . $this->max_len . ' karakters.';
# 2e check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
# omdat this->nickExists in mysql case-insensitive zoek
		} elseif (strtolower($lid->getNickname()) != strtolower($this->getValue()) AND Lid::nickExists($this->getValue())) {
			$this->error = 'Deze bijnaam is al in gebruik.';
		}
		return $this->error === null;
	}

}

/**
 * TelefoonField
 *
 * is valid als er een enigszins op een telefoonnummer lijkende string wordt
 * ingegeven.
 */
class TelefoonField extends InputField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (!preg_match('/^([\d\+\-]{10,20})$/', $this->getValue())) {
			$this->error = 'Geen geldig telefoonnummer.';
		}

		return $this->error === null;
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
		return $this->error === null;
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

}

/**
 * SelectField
 * Basis html-select met een aantal opties.
 *
 * is valid als één van de opties geselecteerd is //TODO: of meerdere
 */
class SelectField extends InputField {

	public $options;
	public $css_options;
	public $size;
	public $multiple; //TODO

	public function __construct($name, $value, $description, array $options, $css_options = array(), $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->css_options = $css_options;
		$this->size = (int) $size;
		$this->multiple = $multiple;
		if (count($this->options) < 1) {
			throw new Exception('Tenminste 1 optie nodig voor selectieveld: ' . $name);
		}
		$this->required = true;
	}

	public function validate() {
		if (!array_key_exists($this->getValue(), $this->options)) {
			if ($this->getValue() !== null) {
				$this->error = 'Onbekende optie gekozen';
			}
			if ($this->size === 1 && !parent::isvalidate()) {
				return false;
			}
		}
		return $this->error === null;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<select origvalue="' . $this->orig_value . '" ';
		if ($this->multiple) {
			echo 'multiple ';
		}
		if ($this->size > 1) {
			echo 'size="' . $this->size . '"';
		}
		echo $this->getAttribute(array('id', 'name', 'class', 'disabled', 'onchange', 'onclick')) . '>';

		foreach ($this->options as $value => $description) {
			echo '<option value="' . $value . '"';
			if ($value == $this->value) {
				echo ' selected="selected"';
			}
			if (array_key_exists($value, $this->css_options)) {
				echo ' class="' . $this->css_options[$value] . '"';
			}
			echo '>' . htmlspecialchars($description) . '</option>';
		}
		echo '</select>';

		echo '</div>';
	}

}

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
			echo '<input type="radio" id="field_' . $this->getName() . '_option_' . $value . '" value="' . $value . '"' . $this->getAttribute(array('name', 'origvalue', 'class', 'disabled', 'onchange', 'onclick'));
			if ($value == $this->value) {
				echo ' checked="checked"';
			}
			echo '><label for="field_' . $this->getName() . '_option_' . $value . '" ' . $this->getAttribute('class') . '> ' . htmlspecialchars($description) . '</label><br />';
		}
		echo '</div>';

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
		return $this->error === null;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		$years = range($this->minyear, $this->maxyear);
		$mounths = range(1, 12);
		$days = range(1, 31);

//als de datum al nul is, moet ie dat ook weer kunnen worden...
		if ($this->getValue() == '0000-00-00' OR $this->getValue() == 0) {
			$years[] = '0000';
			$mounths[] = 0;
			$days[] = 0;
		}

		echo '<select id="field_' . $this->name . '_dag" name="' . $this->name . '_dag" origvalue="' . substr($this->orig_value, 8, 2) . '" ' . $this->getAttribute('class') . '>';
		foreach ($days as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 8, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_maand" name="' . $this->name . '_maand" origvalue="' . substr($this->orig_value, 5, 2) . '" ' . $this->getAttribute('class') . '>';
		foreach ($mounths as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 5, 2)) {
				echo ' selected="selected"';
			}

			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_jaar" name="' . $this->name . '_jaar" origvalue="' . substr($this->orig_value, 0, 4) . '" ' . $this->getAttribute('class') . '>';
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
		return $this->error === null;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		$hours = range(0, 23);
		$minutes = range(0, 59, $this->minutensteps);

		echo '<select id="field_' . $this->name . '_uur" name="' . $this->name . '_uur" origvalue="' . substr($this->orig_value, 0, 2) . '" ' . $this->getAttribute('class') . '>';
		foreach ($hours as $value) {
			$value = sprintf('%02d', $value);
			echo '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 2)) {
				echo ' selected="selected"';
			}
			echo '>' . $value . '</option>';
		}
		echo '</select> ';

		echo '<select id="field_' . $this->name . '_minuut" name="' . $this->name . '_minuut" origvalue="' . substr($this->orig_value, 3, 2) . '" ' . $this->getAttribute('class') . '>';
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

	public $required = false;

	public function getValue() {
		if (parent::isPosted()) {
			return true;
		} else {
			return false;
		}
	}

	public function validate() {
		if ($this->required AND (boolean) $this->getValue() === false) {
			$this->error = 'Dit is een verplicht veld.';
		}
		return $this->error === null;
	}

	public function view() {
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getErrorDiv();

		echo '<input type="checkbox"' . $this->getAttribute(array('id', 'name', 'value', 'origvalue', 'class', 'disabled', 'onchange', 'onclick'));
		if ($this->value) {
			echo ' checked="checked" ';
		}
		echo '/>';

		echo '</div>';
	}

}

/**
 * SubmitButton.
 */
class SubmitButton extends InputField {

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

}

/**
 * Commentaardingen voor formulieren
 */
class HTMLComment extends InputField {

	public $comment;

	public function __construct($comment) {
		$this->comment = $comment;
	}

	public function view() {
		echo $this->comment;
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
