<?php
/*
 * formulier.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
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
 * 		- TextField					Textarea
 * 			* PreviewTextField		Textarea met ubb voorbeeld 
 * 			* AutoresizeTextField	Textarea die automagisch uitbreidt bij typen, lijkt een autoresizing input
 * 		- InputField				Simpele input
 * 			* CountryField			Landjes 
 * 			* UidField				Uid's  met preview
 *			* StudieField
 * 			* EmailField
 * 			* UrlField				Urls
 * 		- LidField					Leden selecteren
 * 		- IntField					Integers 
 * 			* NickField					Nicknames
 * 			* TelefoonField
 * 		- PassField					Wachtwoorden (oude, nieuwe, nieuwe ter bevestiging)
 *		- SelectField
 * 			* GeslachtField
 * 			* JaNeeField
 * 			* VerticaleField		Verticalen
 * 			* KerkField
 * 		- DatumField				Datums (want data is zo ambigu)
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
 * 		'/index.php',
 * 		array(
 * 			InputField('naam', '', 'Naam'),
 * 			PassField('password'),
 * 			SubmitButton('save')
 * 		);
 */

class Formulier{

	private $action;
	private $fields;

	public $cssID;
	public $cssClass='Formulier';
	
	public function __construct($action='', $fields=array()){
		$this->action=$action;
		$this->fields=$fields;
	}
	
	public function setAction($action){
		$this->action=$action;
	}

	public function getFields(){
		return $this->fields;
	}

	public function addFields($fields){
		array_merge($this->fields, $fields);
	}
	
	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted(){
		$posted=false;
		foreach($this->getFields() as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}

	/**
	 * alle valid-functies krijgen een argument mee, wat kan wisselen per
	 * gemaakt formulier.
	 */
	public function valid($extra){
		if(!$this->isPosted()){
			$this->error='Formulier is niet compleet';
			return false;
		}
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->getFields() as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid($extra)){
				$valid=false;
			}
		}
		return $valid;
	}

	public function findByName($fieldname){
		foreach($this->fields as $field){
			if($field->getName() == $fieldname){
				return $field;
			}
		}
		return false;
	}

	/**
	 * Poept het formulier uit, inclusief <form>-tag, en de javascript
	 * voor de autocomplete...
	 */
	public function view($compleetformulier=true){
		if($compleetformulier){
			echo '<form action="'.$this->action.'" id="'.$this->cssID.'" class="'.$this->cssClass.'" method="post">'."\n";
			echo '<script type="text/javascript">if(FieldSuggestions==undefined){var FieldSuggestions=[];} </script>';
		}

		$javascript=array();
		foreach($this->getFields() as $field){
			if($compleetformulier){
				$field->view();
			}
			$js=$field->getJavascript();
			$javascript[md5($js)]=$js."\n";
		}

		echo '<script type="text/javascript">jQuery(document).ready(function($){'."\n".implode($javascript)."\n".'});</script>';
	}

}

/**
 * Alle dingen die we in de field-array van een Formulier stoppen
 * moeten een uitbreiding zijn van FormElement
 */
class FormElement{
	public function getType(){
		return get_class($this);
	}
	public function view(){}
	public function getJavascript(){}
}

/**
 * class FormField is de moeder van input die data leveren.
 */
class FormField extends FormElement{
	public $name;					//naam van het veld in POST
	public $value;					//welke initiele waarde heeft het veld?
	public $notnull=false; 			//mag het veld leeg zijn?
	public $autocomplete=true; 		//browser laten autoaanvullen?
	public $placeholder=null;		//plaats een grijze placeholdertekst in leeg veld
	public $error='';				//foutmelding van dit veld
	
	public $max_len=0;				//maximale lengte van de invoer.
	public $rows=5;					//aantal rijen van textarea

	//array met classnames die later in de class-tag komen.
	public $inputClasses=array('regular');

	//array met suggesties die de javascript-autocomplete aan gaat bieden.
	public $suggestions=array();
	public $remotedatasource='';

	public function __construct($name, $value, $description=null){
		$this->name=$name;
		$this->value=$value;
		$this->description=$description;

		if($this->isPosted()!==false){
			$this->value=$this->getValue();
		}
		
		//add *Field classname to cssclasses
		$this->inputClasses[]=$this->getType();
	}
	public function getType(){	return get_class($this); }
	public function getName(){	return $this->name; }
	public function isPosted(){	return isset($_POST[$this->name]); }

	//een remotedatasource instellen overruled suggestions
	public function setRemoteSuggestionsSource($url){	$this->remotedatasource=$url; }
	public function setSuggestions($array){				$this->suggestions=$array; }
	public function setPlaceholder($bericht){			$this->placeholder=$bericht; }

	public function getValue(){
		if($this->isPosted()){
			return trim($_POST[$this->name]);
		}
		return '';
	}
	
	/**
	 * Is de invoer voor het veld correct?
	 * standaard krijgt deze functie de huidige waarde mee als argument
	 * 
	 * Kindertjes van deze classe kunnen deze methode overloaden om specifiekere
	 * testen mogelijk te maken.
	 */
	public function valid(){
		if(!$this->isPosted()){
			$this->error='Veld is niet gepost.';
		//vallen over lege velden als dat aangezet is voor het veld en als gebruiker geen LEDEN_MOD heeft.
		}elseif($this->notnull AND !LoginLid::instance()->hasPermission('P_LEDEN_MOD') AND $this->getValue()==''){
			$this->error='Dit is een verplicht veld.';
		}
		
		//als max_len > 0 dan checken of de lengte er niet overheen gaat.
		if($this->max_len>0 AND strlen($this->getValue())>$this->max_len){
			$tis->error='Dit veld mag maximaal '.$this->max_len.' tekens lang zijn';
		}
		
		return $this->error=='';
	}

	/**
	 * Elk veld staat in een div, geef de html terug voor de openingstag van die div.
	 */
	protected function getDiv(){
		$cssclass='veld';
		if($this->error!=''){
			$cssclass.=' metfouten';
		}
		return '<div class="'.$cssclass.'" id="'.$this->name.'">';
	}

	/**
	 * Elk veld heeft een label, geef de html voor het label
	 */
	protected function getLabel(){
		if($this->description!=null){
			echo '<label for="field_'.$this->name.'">'.mb_htmlentities($this->description).'</label>';
		}
	}

	/**
	 * Zorg dat de suggesties gegeven gaan worden.
	 */
	protected function getFieldSuggestions(){
		$return='';

		if(count($this->suggestions)>0 OR $this->remotedatasource!=''){
			if($this->remotedatasource!=''){
				$suggestions=$this->remotedatasource;
			}else{
				$suggestions=$this->suggestions;
			}
			$return .= '<script language="javascript"> '."\n";
			$return .= 'FieldSuggestions["'.$this->name.'"]='.json_encode($suggestions)."; \n";
			$return .= '</script>';
		}
		return $return;
	}

	/**
	 * Geef een foutmelding voor dit veld terug.
	 */
	public function getError($html=true){
		if($html===false){
			return $this->error;
		}
		
		if($this->error!=''){
			return '<div class="waarschuwing">'.$this->error.'</div>';
		}
	}

	/**
	 * De input kan allerlei CSS-classes hebben. Geef hier een lijstje
	 * terug...
	 */
	protected function getInputClasses(){
		if($this->remotedatasource!=''){
			$this->inputClasses[]='hasRemoteSuggestions';
		}elseif(count($this->suggestions)>0){
			$this->inputClasses[]='hasSuggestions';
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
	protected function getInputAttribute($attr){
		if(is_array($attr)){
			$return='';
			foreach($attr as $a){
				$return.=$this->getInputAttribute($a).' ';
			}
			return $return;
		}
		switch($attr){
			case 'id': return 'id="field_'.$this->getName().'"'; break;
			case 'class': return 'class="'.implode(' ', $this->getInputClasses()).'"'; break;
			case 'value': return 'value="'.htmlspecialchars($this->value).'"'; break;
			case 'name': return 'name="'.$this->name.'"'; break;
			case 'placeholder': 
				if($this->placeholder!=null){
					return 'placeholder="'.$this->placeholder.'"';
				}
			break;
			case 'rows': 
				if($this->rows>0){
					return 'rows="'.$this->rows.'"'; break;
				}
			break;
			case 'maxlength':
				if($this->max_len>0){
					return 'maxlength="'.$this->max_len.'"';
				}
			break;
			case 'autocomplete':
				if(!$this->autocomplete OR count($this->suggestions)>0 OR $this->remotedatasource!=''){
					return 'autocomplete="off" ';
				}
			break;
		}
		return '';
	}

	/**
	 * view die zou moeten werken voor veel velden...
	 */
	public function view(){
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();
		
		echo '<input type="text" '.$this->getInputAttribute(array('id', 'name', 'class', 'value', 'maxlength', 'placeholder', 'autocomplete')).' />';

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
	 *		array(data:array(..,..,..), value: "string", result:"string"),
	 * 		array(... )
	 * )
	 * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
	 */
	public function getJavascript(){
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
class TextField extends FormField{

	public function view(){
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

		echo '<textarea '.$this->getInputAttribute(array('id', 'name', 'class', 'rows', 'maxlength', 'placeholder', 'autocomplete')).' />';
		echo htmlspecialchars($this->value);
		echo '</textarea>';

		echo $this->getFieldSuggestions();
		echo '</div>';
	}
}
/*
 * een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */
class AutoresizeTextField extends TextField{

	public function __construct($name, $value, $description=null, $max_len=255, $placeholder=null){
		parent::__construct($name, $value, $description);
			$this->max_len=(int)$max_len;
			$this->rows=0;
			$this->inputClasses[]='wantsAutoresize';
			$this->placeholder=$placeholder;
	}

	public function getJavascript(){
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
		hiddenDiv.html(content);  
  
		textarea.css('height', hiddenDiv.height());  
  
	}).keyup();  
});
JS;
	}
}
class RequiredAutoresizeTextField extends AutoresizeTextField{
	public $notnull=true;
}

/**
 * Textarea met een ubb-preview erbij. De hele ubb-preview wordt gemaakt
 * door de javascript in de klasse Formulier, op alle textarea's met
 * de class die hier gegeven wordt.
 *
 * met previewOnEnter() is klikken op het voorbeeld-knopje niet meer
 * nodig, er wordt een voorbeeld gemaakt bij het op enter drukken.
 */
class PreviewTextField extends TextField{

	private $previewOnEnter=false;
	
	public function __construct($name, $value, $description=null){
		parent::__construct($name, $value, $description);
		$this->inputClasses[]='wantsPreview';
	}

	/**
	 * Bij elk keyup-event een nieuwe preview maken.
	 * Genereert dus heel veel evens, niet erg wenselijk, moet dus nog
	 * een time-out in komen...
	 */
	public function previewOnEnter($value=true){
		$this->previewOnEnter=$value;
	}
	/**
	 * Wrap het veld in een divje, maak een preview-div aan voor het veld
	 * met dezelde breedte, en voeg een knopje toe om het event te triggeren.
	 */
	public function getJavascript(){
		$js=<<<JS
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
		if($this->previewOnEnter){
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

class RequiredPreviewTextField extends PreviewTextField{
	public $notnull=true;

}

/**
 * Een InputField is een elementaire input-tag en heeft een maximale
 * lengte.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class InputField extends FormField{
	public $max_len=255;
	
	public function __construct($name, $value, $description, $max_len=255){
		parent::__construct($name, $value, $description);
		$this->max_len=(int)$max_len;
	}
	
	public function valid(){
		if(!parent::valid()){ return false; }
		if($this->getValue()==''){ return true; }
		
		if(mb_strlen($this->getValue())>$this->max_len){
			$this->error='Maximaal '.$this->max_len.' karakters toegestaan.';
		}
		return $this->error=='';
	}
}
class RequiredInputField extends InputField{
	public $notnull=true;
}

/**
 * CountryField met een aantal autocomplete suggesties voor landen.
 * Doet verder geen controle op niet-bestaande landen...
 */
class CountryField extends FormField{
	public function __construct($name, $value, $description){
		parent::__construct($name, $value, $description);
		
		$landSuggesties=array('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
		$this->setSuggestions($landSuggesties);
	}
}

class RequiredCountryField extends CountryField{
	public $notnull=true;
}

/**
 * In een UidField kunnen we een uid invullen.
 * Erachter zal dan de naam komen te staan. Het veld valideert als
 *  - het leeg is.
 *  - het een geldig uid bevat.
 */
class UidField extends InputField{
	public function __construct($name, $value, $description){
		parent::__construct($name, $value, $description, 4);
	}
	public function valid(){
		if(!parent::valid()){ return false; }

		//leeg veld wel accepteren.
		if($this->getValue()==''){ return true; }
		
		//maar als er iets wordt ingevuld, moet het wel een uid zijn.
		if(!Lid::exists($this->getValue())){
			$this->error='Geen geldig uid opgegeven.';
		}
		return $this->error=='';
	}

	/**
	 * Voeg een preview-div toe achter het veld, defenier een keyup-
	 * event op het veld waarin de ajax-request gedaan wordt en trigger
	 * het event meteen om de boel meteen te vullen.
	 */
	public function getJavascript(){
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
class LidField extends FormField{
	// zoekfilter voor door namen2uid gebruikte Zoeker::zoekLeden. 
	// geaccepteerde input: 'leden', 'oudleden', 'alleleden', 'nobodies'
	private $zoekin;

	public function __construct($name, $value, $description=null,$zoekin='leden'){
		$lid=LidCache::getLid($value);
		if($lid instanceof Lid){
			$value=$lid->getNaamLink('full', 'plain');
		}
		parent::__construct($name, $value, $description);
		if(!in_array($zoekin, array('leden', 'oudleden', 'alleleden', 'nobodies'))){
			$zoekin='leden';
		}
		$this->zoekin=$zoekin;
		$this->setRemoteSuggestionsSource('/tools/naamsuggesties/'.$this->zoekin);
		$this->inputClasses[]='wantsLidPreview';
	}
	/**
	 * LidField::getValue() levert altijd een uid of '' op.
	 */
	public function getValue(){
		//leeg veld meteen teruggeven
		if($this->getOriginalValue()==''){ return ''; }
		//uid opzoeken
		if($uid = namen2uid($this->getOriginalValue(), $this->zoekin) AND isset($uid[0]['uid'])){
			return $uid[0]['uid'];
		}
		return '';
	}

	/**
	 * getOriginalValue() levert het ingevoerde.
	 */
	public function getOriginalValue(){
		return parent::getValue();
	}
	/**
	 * checkt of er een uniek lid wordt gevonden
	 */
	public function valid(){
		if(!parent::valid()){ return false; }

		//leeg veld wel accepteren.
		if($this->getOriginalValue()==''){ return true; }

		$uid=namen2uid($this->getOriginalValue(), $this->zoekin);
		if($uid){
			if(isset($uid[0]['uid']) AND Lid::exists($uid[0]['uid'])){
				return true;
			}elseif(count($uid[0]['naamOpties'])>0){ //meerdere naamopties?
				$this->error='Meerdere leden mogelijk';
				return false;
			}
		}
		$this->error='Geen geldig lid';
		return $this->error=='';
	}

	/**
	 * Voeg een preview-div toe achter het veld, defenier een
	 * keyup-event op het veld, en trigger het event meteen om de boel
	 * meteen te vullen.
	 */
	public function getJavascript(){
		$zoekin='';
		$js=parent::getJavascript();
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


class RequiredLidField extends LidField{
	public $notnull=true;
	
	public function valid(){
		if(!parent::valid()){ return false; }
		if($this->getValue()==''){ 
			$this->error= 'Dit is een verplicht veld.';
		}
		return $this->error=='';
	}
}

/**
 * StudieField
 *
 * Suggereert een aantal studies, doet verder geen controle op invoer.
 */
class StudieField extends InputField{
	public function __construct($name, $value, $description){
		parent::__construct($name, $value, $description, 100);

		//de studies aan de TU, even prefixen met 'TU Delft - '
		$tustudies=array('BK', 'CT', 'ET', 'IO', 'LST', 'LR', 'MT', 'MST', 'TA', 'TB', 'TI', 'TN', 'TW', 'WB');
		$tustudies=array_map(create_function('$value', 'return "TU Delft - ".$value;'), $tustudies);
		
		$andere=array('INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden');
		
		$this->setSuggestions(array_merge($tustudies, $andere));
	}
}


class EmailField extends FormField{
	/**
	 * Dikke valideerfunctie voor emails.
	 */
	public function valid(){
		if(!parent::valid()){ return false; }
		if($this->getValue()==''){ return true; }
		//bevat het email-adres een @
		if(strpos($this->getValue(), '@') === false){
			$this->error='Ongeldig formaat email-adres';
		}else{
			# anders gaan we m ontleden en controleren
			list ($usr,$dom) = explode('@', $this->getValue());
			if(mb_strlen($usr) > 50){
				$this->error='Gebruik max. 50 karakters voor de @:';
			} elseif(mb_strlen($dom) > 50){
				$this->error='Gebruik max. 50 karakters na de @:';
			# RFC 821 <- voorlopig voor JabberID even zelfde regels aanhouden
			# http://www.lookuptables.com/
			# Hmmmz, \x2E er uit gehaald ( . )
			}elseif(preg_match('/[^\x21-\x7E]/', $usr) OR preg_match('/[\x3C\x3E\x28\x29\x5B\x5D\x5C\x2C\x3B\x40\x22]/', $usr)){
				$this->error='Het adres bevat ongeldige karakters voor de @:';
			}elseif(!preg_match('/^[a-z0-9]+([-.][a-z0-9]+)*\\.[a-z]{2,4}$/i', $dom)){
				$this->error='Het domein is ongeldig:';
			}elseif(!checkdnsrr($dom, 'A') and !checkdnsrr($dom, 'MX')){
				$this->error='Het domein bestaat niet (IPv4):';
			}elseif(!checkdnsrr($dom, 'MX')){
				$this->error='Het domein is niet geconfigureerd om email te ontvangen:';
			}
		}
		return $this->error=='';
	}
}
class RequiredEmailField extends EmailField{
	public $notnull=true;
}

/**
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends FormField{
	public function valid(){
		if(!parent::valid()){ return false; }
		if($this->getValue()==''){ return true; }

		// controleren of het een geldige url is...
		if(!is_utf8($this->getValue()) OR !preg_match('#([\w]+?://[^ "\n\r\t<]*?)#is', $this->getValue())){
			$this->error='Ongeldige karakters:';
		} elseif($this->max_len!=null && mb_strlen($this->getValue()) > $this->max_len) {
			$this->error='Gebruik maximaal '.$this->max_len.' karakters:';
		}
		return $this->error=='';
	}
}

/**
 * Invoeren van integers. Eventueel met minima/maxima.
 */
class IntField extends FormField{
	public $min=null;
	public $max=null;
	
	public function __construct($name, $value, $description, $max=null, $min=null){
		parent::__construct($name, $value, $description);
		$this->max=(int)$max;
		$this->min=(int)$min;

		$this->notnull=true;
	}
	
	public function getValue(){ return (int)parent::getValue(); }
	
	public function valid(){
		if(!parent::valid()){ return false; }
		//als een veld verplicht is heeft het in FormField::valid() al een foutmelding opgeleverd.
		if($this->getValue()==0){ return true; }
		
		if(!preg_match('/\d+/', $this->getValue())){
			$this->error='Alleen getallen toegestaan';
		}elseif($this->max!==null AND $this->getValue()>$this->max){
			$this->error='Maximale waarde is '.$this->max.' ';
		}elseif($this->min!==null AND $this->getValue()<$this->min){
			$this->error='Minimale waarde is '.$this->min.' ';
		}
		return $this->error=='';
	}
}

/**
 * NickField
 *
 * is pas valid als dit lid de enige is met deze nick.
 */
class NickField extends FormField{
	public $max_len=20;
	public function valid($lid){
		if(!parent::valid()){ return false; }
		
		//lege nicknames vinden we prima.
		if($this->getValue()==''){ return true; }
		
		if(!is_utf8($this->getValue())) {
			$this->error='Ongeldige karakters, gebruik reguliere tekst.';
		}elseif(mb_strlen($this->getValue()) > $this->max_len){
			$this->error='Gebruik maximaal '.$this->max_len.' karakters.';
		# 2e check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		# omdat this->nickExists in mysql case-insensitive zoek
		}elseif(strtolower($lid->getNickname())!=strtolower($this->getValue()) AND Lid::nickExists($this->getValue())) {
			$this->error='Deze bijnaam is al in gebruik.';
		}
		return $this->error=='';
	}
}

/**
 * TelefoonField
 *
 * is valid als er een enigszins op een telefoonnummer lijkende string wordt
 * ingegeven.
 */
class TelefoonField extends InputField{
	public function valid(){
		if(!parent::valid()){ return false; }
		if($this->getValue()==''){ return true; }
		if(!preg_match('/^([\d\+\-]{10,20})$/', $this->getValue())){
			$this->error='Geen geldig telefoonnummer.';
		}		

		return $this->error=='';
	}
}

/**
 * PassField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 */
class PassField extends FormField{
	public function __construct($name){
		$this->name=$name;
	}
	public function isPosted(){
		return isset($_POST[$this->name.'_current'], $_POST[$this->name.'_new'], $_POST[$this->name.'_confirm']);
	}
	public function getValue(){
		if($this->isPosted()){
			return $_POST[$this->name.'_new'];
		}
		return false;
	}
		
	public function valid($lid){
		if(!$lid instanceof Lid){
			throw new Exception($this->getType().'::valid() moet een Lid-object meekrijgen');
		}
		if(!parent::valid()){ return false; }
		$current=$_POST[$this->name.'_current'];
		$new=$_POST[$this->name.'_new'];
		$confirm=$_POST[$this->name.'_confirm'];
		if($current!=''){
			if(!$lid->checkpw($current)){
				$this->error='Uw huidige wachtwoord is niet juist';
			}else{
				if($new=='' OR $confirm==''){
					$this->error='Vul uw nieuwe wachtwoord twee keer in';
				}elseif($new!=$confirm){
					$this->error='Nieuwe wachtwoorden komen niet overeen';
				}elseif(preg_match('/^[0-9]*$/', $new)) {
		            $this->error='Het nieuwe wachtwoord moet ook letters of leestekens bevatten... :-|';
				}elseif(mb_strlen($new) < 6 OR mb_strlen($new) > 60){
					$this->error='Het wachtwoord moet minimaal 6 en maximaal 16 tekens bevatten';
				}
			}
		}
		if($new!='' AND $current==''){
			$this->error='U dient uw huidige wachtwoord ook in te voeren';
		}
		return $this->error=='';
	}
	public function view(){
		echo $this->getDiv();
		echo '<div class="password">';
		echo $this->getError();
		echo '<label for="field_'.$this->name.'_current">Huidige wachtwoord</label>';
		echo '<input type="password" autocomplete="off" id="field_'.$this->name.'_current" name="'.$this->name.'_current" /></div>';
		echo '<div class="password"><label for="field_'.$this->name.'_new">Nieuw wachtwoord</label>';
		echo '<input type="password" autocomplete="off" id="field_'.$this->name.'_new" name="'.$this->name.'_new" /></div>';
		echo '<div class="password"><label for="field_'.$this->name.'_confirm">Nogmaals</label>';
		echo '<input type="password" autocomplete="off" id="field_'.$this->name.'_confirm" name="'.$this->name.'_confirm" /></div>';
		echo '</div>';
	}
	public function getJavascript(){
		return '';
	}
}

/**
 * SelectField
 * Basis html-select met een aantal opties.
 *
 * is valid als één van de opties geselecteerd is.
 */
class SelectField extends FormField{
	public $options=array();
	
	public function __construct($name, $value, $description, $options){
		parent::__construct($name, $value, $description);
		$this->options=$options;
		if(count($this->options)<2){
			throw new Exception('Tenminste twee opties nodig.');
		}
		$this->notnull=true;
	}
	public function valid(){
		if(!parent::valid()){ return false; }
		if(!array_key_exists($this->getValue(), $this->options)){
			$this->error='Onbekende optie gekozen';
		}
		return $this->error=='';
	}
	public function view(){
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();
	
		echo '<select '.$this->getInputAttribute(array('id', 'name', 'class')).' />';
		
		foreach($this->options as $value => $description){
			echo '<option value="'.$value.'"';
			if($value==$this->value){
				echo 'selected="selected" ';
			}
			echo '>'.htmlspecialchars($description).'</option>';
		}
		echo '</select>';
		
		echo '</div>';
	}
}

/**
 * Man of vrouw
 */
class GeslachtField extends SelectField{
	public function __construct($name, $value, $description=null){
		parent::__construct($name, $value, $description, array('m'=> 'Man', 'v'=>'Vrouw'));
	}
}

/**
 * Ja of Nee
 */
class JaNeeField extends SelectField{
	public function __construct($name, $value, $description=null){
		parent::__construct($name, $value, $description, array('ja'=> 'Ja', 'nee'=>'Nee'));
	}
}

/**
 * Selecteer een verticale. Geeft een volgnummer terug.
 */
class VerticaleField extends SelectField{
	public function __construct($name, $value, $description=null){
		require_once 'verticale.class.php';
		$verticalen=Verticale::getNamen();
		parent::__construct($name, $value, $description, $verticalen);
	}
}

class KerkField extends SelectField{
	public function __construct($name, $value, $description=null){
		$kerken=array(
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
class DatumField extends FormField{
	protected $maxyear;

	public function __construct($name, $value, $description, $maxyear=null){
		parent::__construct($name, $value, $description);
		if($maxyear===null){
			$this->maxyear=date('Y');
		}else{
			$this->maxyear=(int)$maxyear;
		}
	}
	public function isPosted(){
		return isset($_POST[$this->name.'_jaar'], $_POST[$this->name.'_maand'], $_POST[$this->name.'_dag']);
	}

	public function getJaar(){ return $_POST[$this->name.'_jaar']; }
	public function getMaand(){ return $_POST[$this->name.'_maand']; }
	public function getDag(){ return $_POST[$this->name.'_dag']; }

	public function getValue(){
		if($this->isPosted()){
			return $this->getJaar().'-'.$this->getMaand().'-'.$this->getDag();
		}else{
			return parent::getValue();
		}
	}
	
	
	public function valid(){
		if(!parent::valid()){ return false; }
		if(!preg_match('/^(\d{4})-(\d\d?)-(\d\d?)$/', $this->getValue())){
			$this->error='Ongeldige datum';
		}elseif(substr($this->getValue(), 0, 4)>$this->maxyear){
			$this->error='Er kunnen geen data later dan '.$this->maxyear.' worden weergegeven';
		}elseif($this->getValue()!='0000-00-00' AND !checkdate($this->getMaand(), $this->getDag(), $this->getJaar())){
			$this->error='Datum bestaat niet';
		}
		return $this->error=='';
	}

	public function view(){
		echo $this->getDiv();
		echo $this->getLabel();
		echo $this->getError();

		$years=range(1940, $this->maxyear);
		$mounths=range(1,12);
		$days=range(1,31);
	
		//als de datum al nul is, moet ie dat ook weer kunnen worden...
		if($this->getValue()=='0000-00-00' OR $this->getValue()==0){
			$years[]='0000';
			$mounths[]=0;
			$days[]=0;
		}

		echo '<select id="field_'.$this->name.'_jaar" name="'.$this->name.'_jaar" '.$this->getInputAttribute('class').' />';
		foreach($years as $value){
			echo '<option value="'.$value.'"';
			if($value==substr($this->value, 0,4)){
				echo 'selected="selected" ';
			}
			echo '>'.$value.'</option>';
		}
		echo '</select>&nbsp;';

		echo '<select id="field_'.$this->name.'_maand" name="'.$this->name.'_maand" '.$this->getInputAttribute('class').' />';
		foreach($mounths as $value){
			$value=sprintf('%02d', $value);
			echo '<option value="'.$value.'"';
			if($value==substr($this->value, 5, 2)){
				echo 'selected="selected" ';
			}

			echo '>'.$value.'</option>';
		}
		echo '</select>&nbsp;';

		echo '<select id="field_'.$this->name.'_dag" name="'.$this->name.'_dag" '.$this->getInputAttribute('class').' />';
		foreach($days as $value){
			$value=sprintf('%02d', $value);
			echo '<option value="'.$value.'"';
			if($value==substr($this->value, 8, 2)){
				echo 'selected="selected" ';
			}
			echo '>'.$value.'</option>';
		}
		echo '</select>';
		echo '</div>';
	}
}

/**
 * SubmitButton.
 */
class SubmitButton extends FormElement{
	protected $buttontext;
	protected $extra='';
	
	public function __construct($buttontext='opslaan', $extra=''){
		$this->buttontext=$buttontext;
		$this->extra=$extra;
	}
	
	public function view(){
		echo <<<HTML
<div class="submit">
	<label for="submit">&nbsp;</label>
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
class HTMLComment extends FormElement{
	public $comment;

	public function __construct($comment){
		$this->comment=$comment;
	}

	public function view(){
		echo $this->comment;
	}
}
class UBBComment extends HTMLComment{
	public function view(){
		echo CsrUBB::instance()->getHtml($this->comment);
	}
}
class Comment extends HTMLComment{
	public function view(){
		echo '<h3>'.$this->comment.'</h3>';
	}
}
