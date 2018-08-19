<?php

namespace CsrDelft\view\profiel;

use CsrDelft\lid\LidZoeker;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\OntvangtContactueel;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\CollapsableSubkopje;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\getalvelden\RequiredTelefoonField;
use CsrDelft\view\formulier\getalvelden\TelefoonField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\LandField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\RequiredIBANField;
use CsrDelft\view\formulier\invoervelden\RequiredLandField;
use CsrDelft\view\formulier\invoervelden\RequiredTextareaField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\StudieField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\formulier\keuzevelden\DateField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\RequiredDateField;
use CsrDelft\view\formulier\keuzevelden\RequiredGeslachtField;
use CsrDelft\view\formulier\keuzevelden\RequiredSelectField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\keuzevelden\VerticaleField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\toestemming\ToestemmingModalForm;

/**
 * @property ProfielModel $model
 */
class ProfielForm extends Formulier {

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » ' . $this->model->getLink('civitas');
	}

	public function __construct(Profiel $profiel) {
		if ($profiel->uid) {
			parent::__construct($profiel, '/profiel/' . $profiel->uid . '/bewerken');
		} else {
			parent::__construct($profiel, '/profiel/' . $profiel->lidjaar . '/nieuw/' . strtolower(substr($profiel->status, 2)));
		}

		$admin = LoginModel::mag('P_LEDEN_MOD');
		$inschrijven = !$profiel->getAccount();

		if ($inschrijven) {
			$this->titel = 'Welkom bij C.S.R.!';
			$fields[] = new HtmlComment('<p>
				Hieronder mag je gegevens invullen in het databeest van de Civitas. Zo kunnen we contact met je houden,
				kunnen andere leden opzoeken waar je woont en kun je (na het novitiaat) op het forum berichten plaatsen.
			</p>');
		} else {
			$this->titel = 'Bewerk het profiel van ' . $profiel->getNaam('volledig');
			$fields[] = new HtmlComment('<p>
				Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
				wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
				een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
				dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:vice-abactis@csrdelft.nl">Vice-Abactis</a>.
			</p>');
		}
		$fields[] = new HtmlComment('<p>
			Als er <span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten aanpassen aan het
			gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.
		</p>');

		if ($admin) {
			$statussen = array();
			foreach (LidStatus::getTypeOptions() as $optie) {
				$statussen[$optie] = LidStatus::getDescription($optie);
			}
			$fields[] = new SelectField('status', $profiel->status, 'Lidstatus', $statussen);
			$fields[] = new HtmlComment('<p>Bij het wijzigen van de lidstatus worden overbodige <span class="waarschuwing">gegevens verwijderd</span>, onomkeerbaar, opletten dus!</p>');

			if ($profiel->voornaam == '') {
				$gelijknamigenovieten = array();
			} else {
				$gelijknamigenovieten = LidZoeker::zoekLeden($profiel->voornaam, 'voornaam', 'alle', 'achternaam', array(LidStatus::Noviet), array('uid'));
			}
			if ($profiel->achternaam == '') {
				$gelijknamigeleden = array();
			} else {
				$gelijknamigeleden = LidZoeker::zoekLeden($profiel->achternaam, 'achternaam', 'alle', 'lidjaar', array(LidStatus::Lid, LidStatus::Gastlid), array('uid'));
			}

			$html = '<div class="novieten">';
			if (count($gelijknamigenovieten) > 1 OR ($profiel->status !== LidStatus::Noviet AND !empty($gelijknamigenovieten))) {
				$html .= 'Gelijknamige novieten:<ul class="nobullets">';
				foreach ($gelijknamigenovieten as $noviet) {
					$html .= '<li>' . ProfielModel::getLink($noviet['uid'], 'volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen novieten met overeenkomstige namen.';
			}
			$html .= '</div><div class="leden">';
			if (count($gelijknamigeleden) > 1 OR (!($profiel->status == LidStatus::Lid OR $profiel->status == LidStatus::Gastlid) AND !empty($gelijknamigeleden))) {
				$html .= 'Gelijknamige (gast)leden:<ul class="nobullets">';
				foreach ($gelijknamigeleden as $lid) {
					$html .= '<li>' . ProfielModel::getLink($lid['uid'], 'volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen (gast)leden met overeenkomstige namen.';
			}
			$html .= '</div>';

			$fields[] = new HtmlComment($html);
		}

		if ($admin OR $inschrijven OR $profiel->isOudlid()) {
			$fields[] = new Subkopje('Identiteit');
			$fields[] = new RequiredTextField('voornaam', $profiel->voornaam, 'Voornaam', 50);
			$fields[] = new RequiredTextField('voorletters', $profiel->voorletters, 'Voorletters', 10);
			$fields[] = new TextField('tussenvoegsel', $profiel->tussenvoegsel, 'Tussenvoegsel', 15);
			$fields[] = new RequiredTextField('achternaam', $profiel->achternaam, 'Achternaam', 50);
			if ($admin OR $inschrijven) {
				$fields[] = new RequiredGeslachtField('geslacht', $profiel->geslacht, 'Geslacht');
				$fields[] = new TextField('voornamen', $profiel->voornamen, 'Voornamen', 100);
				if (!$inschrijven) {
					$fields[] = new TextField('postfix', $profiel->postfix, 'Postfix', 7);
					$fields[] = new TextField('nickname', $profiel->nickname, 'Bijnaam', 20);
				}
			}
			$fields[] = new RequiredDateField('gebdatum', $profiel->gebdatum, 'Geboortedatum', date('Y') - 15);
			if ($admin AND $profiel->status === LidStatus::Overleden) {
				$fields[] = new DateField('sterfdatum', $profiel->sterfdatum, 'Overleden op');
			}
			if (($admin OR $profiel->isOudlid() OR $profiel->status === LidStatus::Overleden) AND !$inschrijven) {
				$fields[] = new LidField('echtgenoot', $profiel->echtgenoot, 'Echtgenoot', 'allepersonen');
				$fields[] = new Subkopje('Oudledenpost');
				$fields[] = new TextField('adresseringechtpaar', $profiel->adresseringechtpaar, 'Tenaamstelling post echtpaar', 250);

				$contactueel = array();
				foreach (OntvangtContactueel::getTypeOptions() as $optie) {
					$contactueel[$optie] = OntvangtContactueel::getDescription($optie);
				}
				$fields[] = new SelectField('ontvangtcontactueel', $profiel->ontvangtcontactueel, 'Ontvangt Contactueel?', $contactueel);
			}
		}

		$fields[] = new Subkopje('Adres');
		$fields[] = new RequiredTextField('adres', $profiel->adres, 'Straatnaam + Huisnummer', 100);
		$fields[] = new RequiredTextField('postcode', $profiel->postcode, 'Postcode', 20);
		$fields[] = new RequiredTextField('woonplaats', $profiel->woonplaats, 'Woonplaats', 50);
		$fields[] = new RequiredLandField('land', $profiel->land, 'Land');

		if (!$profiel->isOudlid()) {
			$fields[] = new Subkopje('Adres ouders');
			$fields[] = new TextField('o_adres', $profiel->o_adres, 'Straatnaam + Huisnummer', 100);
			$fields[] = new TextField('o_postcode', $profiel->o_postcode, 'Postcode', 20);
			$fields[] = new TextField('o_woonplaats', $profiel->o_woonplaats, 'Woonplaats', 50);
			$fields[] = new LandField('o_land', $profiel->o_land, 'Land');
			$fields[] = new TelefoonField('o_telefoon', $profiel->o_telefoon, 'Telefoonnummer', 20);
		}

		$fields[] = new Subkopje('Contact');
		//TODO: email & multiple contacts
		$fields['email'] = new RequiredEmailField('email', $profiel->email, 'E-mailadres');
		if (!$inschrijven) {
			$fields['email']->readonly = true;
			$fields['email']->title = 'Wijzig je e-mailadres met het inloggegevens-formulier.';
			$fields[] = new UrlField('linkedin', $profiel->linkedin, 'Publiek LinkedIn-profiel');
			$fields[] = new UrlField('website', $profiel->website, 'Website');
		}
		$fields[] = new RequiredTelefoonField('mobiel', $profiel->mobiel, 'Mobiel', 20);
		$fields[] = new TelefoonField('telefoon', $profiel->telefoon, 'Telefoonnummer (vast)', 20);

		$fields[] = new Subkopje('Boekhouding');
		$fields[] = new RequiredIBANField('bankrekening', $profiel->bankrekening, 'Bankrekening', 18);
		if ($admin) {
			$fields[] = new JaNeeField('machtiging', $profiel->machtiging, 'Machtiging getekend?');
		}

		$fields[] = new Subkopje('Studie');
		$fields[] = new StudieField('studie', $profiel->studie, 'Studie');
		$fields['studiejaar'] = new IntField('studiejaar', (int)$profiel->studiejaar, 'Beginjaar studie', 1950, date('Y'));
		$fields['studiejaar']->leden_mod = $admin;

		if (!$profiel->isOudlid()) {
			$fields[] = new TextField('studienr', $profiel->studienr, 'Studienummer (TU)', 20);
		}

		if (!$inschrijven AND ($admin OR $profiel->isOudlid())) {
			$fields[] = new TextField('beroep', $profiel->beroep, 'Beroep/werk', 4096);
			$fields[] = new IntField('lidjaar', (int)$profiel->lidjaar, 'Lid sinds', 1950, date('Y'));
			$fields[] = new DateField('lidafdatum', $profiel->lidafdatum, 'Lid-af sinds');
		}

		if ($admin AND !$inschrijven) {
			$fields[] = new VerticaleField('verticale', $profiel->verticale, 'Verticale');
			if ($profiel->isLid()) {
				$fields[] = new JaNeeField('verticaleleider', $profiel->verticaleleider, 'Verticaleleider');
				$fields[] = new JaNeeField('kringcoach', $profiel->kringcoach, 'Kringcoach');
			}
			$fields[] = new LidField('patroon', $profiel->patroon, 'Patroon', 'allepersonen');
		}

		$fields[] = new Subkopje('Persoonlijk');
		$fields[] = new TextField('eetwens', $profiel->eetwens, 'Dieet/voedselallergie');
		$fields[] = new RequiredIntField('lengte', (int)$profiel->lengte, 'Lengte (cm)', 50, 250);
		$fields[] = new RequiredSelectField('ovkaart', $profiel->ovkaart, 'OV-kaart', array('' => 'Kies...', 'geen' => '(Nog) geen OV-kaart', 'week' => 'Week', 'weekend' => 'Weekend', 'niet' => 'Niet geactiveerd'));
		$fields[] = new TextField('kerk', $profiel->kerk, 'Kerk', 50);
		$fields[] = new TextField('muziek', $profiel->muziek, 'Muziekinstrument', 50);
		$fields[] = new SelectField('zingen', $profiel->zingen, 'Zingen', array('' => 'Kies...', 'ja' => 'Ja, ik zing in een band/koor', 'nee' => 'Nee, ik houd niet van zingen', 'soms' => 'Alleen onder de douche', 'anders' => 'Anders'));

		if ($admin OR $inschrijven) {
			$fields[] = new TextField('vrienden', $profiel->vrienden, 'Vrienden binnnen C.S.R.', 300);
			$fields[] = new RequiredTextField('middelbareSchool', $profiel->middelbareSchool, 'Middelbare school', 200);
		}

		if ($inschrijven) {
			// Zorg ervoor dat toestemming bij inschrijven wordt opgegeven.
			$fields[] = new Subkopje('Privacy');
			$fields[] = new HiddenField('toestemming_geven', 'true');
			$fields = array_merge($fields, (new ToestemmingModalForm(true))->getFields());
		}

		$fields[] = new Subkopje('<b>Einde vragenlijst</b><br /><br /><br /><br /><br />');
		if ($admin OR LoginModel::mag('commissie:NovCie')) {
			$fields[] = new CollapsableSubkopje('novcieForm', 'In te vullen door NovCie', true);
			$fields[] = new RequiredTextareaField('novitiaat', $profiel->novitiaat, 'Wat verwacht Noviet van novitiaat?');
			$fields[] = new RequiredSelectField('novietSoort', $profiel->novietSoort, 'Soort Noviet', array('noviet', 'nanoviet'));
			$fields[] = new RequiredSelectField('matrixPlek', $profiel->matrixPlek, 'Matrix plek', array('voor', 'midden', 'achter'));
			$fields[] = new RequiredSelectField('startkamp', $profiel->startkamp, 'Startkamp', array('ja', 'nee'));
			$fields[] = new TextareaField('medisch', $profiel->medisch, 'medisch (NB alleen als relevant voor hele NovCie, bijv. allergieen)');
			$fields[] = new TextareaField('novitiaatBijz', $profiel->novitiaatBijz, 'Bijzonderheden novitiaat (op dag x ...)');
			$fields[] = new TextareaField('kgb', $profiel->kgb, 'Overige NovCie-opmerking');
			$fields[] = new HtmlComment('</div>');
		}
		$fields[] = new FormDefaultKnoppen('/profiel/' . $profiel->uid);

		$this->addFields($fields);
	}

}
