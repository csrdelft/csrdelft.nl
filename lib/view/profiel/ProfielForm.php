<?php

namespace CsrDelft\view\profiel;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\CollapsableSubkopje;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\getalvelden\TelefoonField;
use CsrDelft\view\formulier\invoervelden\EmailField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\IBANField;
use CsrDelft\view\formulier\invoervelden\LandField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLandField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\StudieField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\formulier\keuzevelden\DateObjectField;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateObjectField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredEnumSelectField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\keuzevelden\VerticaleField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use CsrDelft\view\Icon;

/**
 * @property ProfielRepository $model
 */
class ProfielForm extends Formulier
{
	public function getBreadcrumbs()
	{
		return '<ol class="breadcrumb"><li class="breadcrumb-item"><a href="/">' .
			Icon::getTag('home') .
			'</a></li>' .
			'<li class="breadcrumb-item"><a href="/ledenlijst">Leden</a></li>' .
			'<li class="breadcrumb-item">' .
			$this->model->getLink('civitas') .
			'</li></ol>';
	}

	public function __construct(Profiel $profiel, $inschrijven)
	{
		if ($profiel->uid) {
			parent::__construct($profiel, '/profiel/' . $profiel->uid . '/bewerken');
		} else {
			parent::__construct(
				$profiel,
				'/profiel/' .
					$profiel->lidjaar .
					'/nieuw/' .
					strtolower(substr($profiel->status, 2))
			);
		}

		$admin = LoginService::mag(P_LEDEN_MOD);
		$novCie = LoginService::mag('commissie:NovCie');

		$fields = [];
		if ($inschrijven) {
			$this->titel = 'Welkom bij C.S.R.!';
			$fields[] = new HtmlComment('<p>
				Hieronder mag je gegevens invullen in het databeest van de Civitas. Zo kunnen we contact met je houden,
				kunnen andere leden opzoeken waar je woont en kun je (na het novitiaat) op het forum berichten plaatsen.
			</p>');
		} else {
			$this->titel = 'Bewerk het profiel van ' . $profiel->getNaam('volledig');
			$fields[] = new HtmlComment(
				'<p>
				Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
				wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
				een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
				dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:' .
					$_ENV['EMAIL_VAB'] .
					'">Vice-Abactis</a>.
			</p>'
			);
		}
		$fields[] = new HtmlComment('<p>
			Als er <span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
			betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten aanpassen aan het
			gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.
		</p>');

		if ($admin) {
			$statussen = [];
			foreach (LidStatus::getEnumValues() as $optie) {
				$statussen[$optie] = LidStatus::from($optie)->getDescription();
			}
			$fields[] = new SelectField(
				'status',
				$profiel->status,
				'Lidstatus',
				$statussen
			);
			$fields[] = new HtmlComment(
				'<p>Bij het wijzigen van de lidstatus worden overbodige <span class="waarschuwing">gegevens verwijderd</span>, onomkeerbaar, opletten dus!</p>'
			);

			$profielService = ContainerFacade::getContainer()->get(
				ProfielService::class
			);

			if ($profiel->voornaam == '') {
				$gelijknamigenovieten = [];
			} else {
				$gelijknamigenovieten = $profielService->zoekLeden(
					$profiel->voornaam,
					'voornaam',
					'alle',
					'achternaam',
					[LidStatus::Noviet]
				);
			}
			if ($profiel->achternaam == '') {
				$gelijknamigeleden = [];
			} else {
				$gelijknamigeleden = $profielService->zoekLeden(
					$profiel->achternaam,
					'achternaam',
					'alle',
					'lidjaar',
					[LidStatus::Lid, LidStatus::Gastlid]
				);
			}

			$html = '<div class="novieten">';
			if (
				count($gelijknamigenovieten) > 1 ||
				($profiel->status !== LidStatus::Noviet &&
					!empty($gelijknamigenovieten))
			) {
				$html .= 'Gelijknamige novieten:<ul class="nobullets">';
				foreach ($gelijknamigenovieten as $noviet) {
					$html .= '<li>' . $noviet->getLink('volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen novieten met overeenkomstige namen.';
			}
			$html .= '</div><div class="leden">';
			if (
				count($gelijknamigeleden) > 1 ||
				(!(
					$profiel->status == LidStatus::Lid ||
					$profiel->status == LidStatus::Gastlid
				) &&
					!empty($gelijknamigeleden))
			) {
				$html .= 'Gelijknamige (gast)leden:<ul class="nobullets">';
				foreach ($gelijknamigeleden as $lid) {
					$html .= '<li>' . $lid->getLink('volledig') . '</li>';
				}
				$html .= '</ul>';
			} else {
				$html .= 'Geen (gast)leden met overeenkomstige namen.';
			}
			$html .= '</div>';

			$fields[] = new HtmlComment($html);
		}
		$fields[] = new Subkopje('Identiteit');
		if ($admin || $inschrijven || $profiel->isOudlid()) {
			$fields[] = new RequiredTextField(
				'voornaam',
				$profiel->voornaam,
				'Voornaam',
				50
			);
			$fields[] = new RequiredTextField(
				'voorletters',
				$profiel->voorletters,
				'Voorletters',
				10
			);
			$fields[] = new TextField(
				'tussenvoegsel',
				$profiel->tussenvoegsel,
				'Tussenvoegsel',
				15
			);
			$fields[] = new RequiredTextField(
				'achternaam',
				$profiel->achternaam,
				'Achternaam',
				50
			);
		}
		$fields['bijnaam'] = new TextField(
			'nickname',
			$profiel->nickname,
			'Bijnaam',
			255
		);
		$fields[
			'bijnaam'
		]->title = "Bijnaam is zichtbaar op profiel, kan op worden gezocht in de zoekbalk en wordt
		weergegeven op het forum bij gebruikers waarbij als profielinstelling de naamweergave 'bijnaam' is ingesteld.";
		if ($admin || $inschrijven || $profiel->isOudlid()) {
			if ($admin || $inschrijven) {
				$fields[] = new RequiredEnumSelectField(
					'geslacht',
					$profiel->geslacht,
					'Geslacht',
					Geslacht::class
				);
				$fields[] = new TextField(
					'voornamen',
					$profiel->voornamen,
					'Voornamen',
					100
				);
				if (!$inschrijven) {
					$fields[] = new TextField('postfix', $profiel->postfix, 'Postfix', 7);
				}
			}
			$fields[] = new RequiredDateObjectField(
				'gebdatum',
				$profiel->gebdatum,
				'Geboortedatum',
				date('Y') - 15,
				1900
			);
			if ($admin && $profiel->status === LidStatus::Overleden) {
				$fields[] = new DateObjectField(
					'sterfdatum',
					$profiel->sterfdatum,
					'Overleden op'
				);
			}
			if (
				($admin ||
					$profiel->isOudlid() ||
					$profiel->status === LidStatus::Overleden) &&
				!$inschrijven
			) {
				$fields[] = new LidField(
					'echtgenoot',
					$profiel->echtgenoot,
					'Echtgenoot',
					'allepersonen'
				);
				$fields[] = new Subkopje('Oudledenpost');
				$fields[] = new TextField(
					'adresseringechtpaar',
					$profiel->adresseringechtpaar,
					'Tenaamstelling post echtpaar',
					250
				);

				$fields[] = new EnumSelectField(
					'ontvangtcontactueel',
					$profiel->ontvangtcontactueel,
					'Ontvangt Contactueel?',
					OntvangtContactueel::class
				);
			}
		}

		$fields[] = new Subkopje('Adres');
		$fields[] = new RequiredTextField(
			'adres',
			$profiel->adres,
			'Straatnaam + Huisnummer',
			100
		);
		$fields[] = new RequiredTextField(
			'postcode',
			$profiel->postcode,
			'Postcode',
			20
		);
		$fields[] = new RequiredTextField(
			'woonplaats',
			$profiel->woonplaats,
			'Woonplaats',
			50
		);
		$fields[] = new RequiredLandField('land', $profiel->land, 'Land');

		if ($profiel->propertyMogelijk('o_adres') || $inschrijven) {
			$fields[] = new Subkopje('Adres ouders');
			$fields[] = new TextField(
				'o_adres',
				$profiel->o_adres,
				'Straatnaam + Huisnummer',
				100
			);
			$fields[] = new TextField(
				'o_postcode',
				$profiel->o_postcode,
				'Postcode',
				20
			);
			$fields[] = new TextField(
				'o_woonplaats',
				$profiel->o_woonplaats,
				'Woonplaats',
				50
			);
			$fields[] = new LandField('o_land', $profiel->o_land, 'Land');
			$fields[] = new TelefoonField(
				'o_telefoon',
				$profiel->o_telefoon,
				'Telefoonnummer',
				20
			);
		}

		$fields[] = new Subkopje('Contact');
		//TODO: email & multiple contacts
		$fields['email'] = new RequiredEmailField(
			'email',
			$profiel->email,
			'E-mailadres'
		);
		$fields['sec_email'] = new EmailField(
			'sec_email',
			$profiel->sec_email,
			'Secundair e-mailadres'
		);
		if (!$inschrijven) {
			$fields['email']->readonly = true;
			$fields['email']->required = false;
			$fields['email']->title =
				'Wijzig je e-mailadres met het inloggegevens-formulier.';
			$fields[] = new UrlField(
				'linkedin',
				$profiel->linkedin,
				'Publiek LinkedIn-profiel'
			);
			$fields[] = new UrlField('website', $profiel->website, 'Website');
		}
		// Mobiel & telefoon, mobiel verplicht voor (nieuwe) leden
		$fields['mobiel'] = new TelefoonField(
			'mobiel',
			$profiel->mobiel,
			'Mobiel',
			20
		);
		$fields['mobiel']->required = $inschrijven || $profiel->isLid();
		$fields[] = new TelefoonField(
			'telefoon',
			$profiel->telefoon,
			'Telefoonnummer (vast)',
			20
		);

		$fields[] = new Subkopje('Boekhouding');

		// Bankrekeningnummer: verplicht voor (nieuwe) leden
		$fields['bankrekening'] = new IBANField(
			'bankrekening',
			$profiel->bankrekening,
			'Bankrekening',
			34
		);
		$fields['bankrekening']->required = $inschrijven || $profiel->isLid();

		if ($admin && !$inschrijven) {
			$fields[] = new JaNeeField(
				'machtiging',
				$profiel->machtiging,
				'Machtiging getekend?'
			);
		}

		$fields[] = new Subkopje('Studie');
		$fields['studie'] = new StudieField('studie', $profiel->studie, 'Studie');
		$fields['studie']->title = 'Onderwijsinstelling - Studie';
		$fields['studiejaar'] = new IntField(
			'studiejaar',
			(int) $profiel->studiejaar,
			'Beginjaar studie',
			1950,
			date('Y')
		);
		$fields['studiejaar']->leden_mod = $admin;

		if (!$inschrijven && ($admin || $profiel->isOudlid())) {
			$fields[] = new TextField(
				'beroep',
				$profiel->beroep,
				'Beroep/werk',
				4096
			);
			$fields[] = new IntField(
				'lidjaar',
				(int) $profiel->lidjaar,
				'Lid sinds',
				1950,
				date('Y')
			);
			$fields[] = new DateObjectField(
				'lidafdatum',
				$profiel->lidafdatum,
				'Lid-af sinds'
			);
		}

		if ($admin && !$inschrijven) {
			$fields[] = new VerticaleField(
				'verticale',
				$profiel->verticale,
				'Verticale'
			);
			if ($profiel->isLid()) {
				$fields[] = new JaNeeField(
					'verticaleleider',
					$profiel->verticaleleider,
					'Verticaleleider'
				);
				$fields[] = new JaNeeField(
					'kringcoach',
					$profiel->kringcoach,
					'Kringcoach'
				);
			}
			$fields[] = new LidField(
				'patroon',
				$profiel->patroon,
				'Patroon',
				'allepersonen'
			);
			$fields[] = new TextField(
				'profielOpties',
				$profiel->profielOpties,
				'Profielopties'
			);
		}

		$fields[] = new Subkopje('Persoonlijk');
		$fields[] = new TextField(
			'eetwens',
			$profiel->eetwens,
			'Dieet/voedselallergie'
		);
		$fields[] = new RequiredIntField(
			'lengte',
			(int) $profiel->lengte,
			'Lengte (cm)',
			50,
			250
		);
		$fields[] = new TextField('kerk', $profiel->kerk, 'Kerk', 50);
		$fields[] = new TextField(
			'muziek',
			$profiel->muziek,
			'Muziekinstrument',
			50
		);
		$fields[] = new SelectField('zingen', $profiel->zingen, 'Zingen', [
			'' => 'Kies...',
			'ja' => 'Ja, ik zing in een band/koor',
			'nee' => 'Nee, ik houd niet van zingen',
			'soms' => 'Alleen onder de douche',
			'anders' => 'Anders',
		]);

		if ($admin || $inschrijven || $novCie) {
			$fields[] = new TextField(
				'vrienden',
				$profiel->vrienden,
				'Vrienden binnnen C.S.R.',
				300
			);
			$fields['middelbareSchool'] = new TextField(
				'middelbareSchool',
				$profiel->middelbareSchool,
				'Middelbare school',
				200
			);
			$fields['middelbareSchool']->required = $inschrijven;
		}

		if ($inschrijven) {
			// Zorg ervoor dat toestemming bij inschrijven wordt opgegeven.
			$fields[] = new Subkopje('Privacy');
			$fields[] = new HiddenField('toestemming_geven', 'true');
			$lidToestemmingRepository = ContainerFacade::getContainer()->get(
				LidToestemmingRepository::class
			);
			$fields = array_merge(
				$fields,
				(new ToestemmingModalForm($lidToestemmingRepository, true))->getFields()
			);
		}

		$fields[] = new Subkopje(
			'<b>Einde vragenlijst</b><br /><br /><br /><br /><br />'
		);
		if (
			($admin || $novCie) &&
			($profiel->propertyMogelijk('novitiaat') || $inschrijven)
		) {
			$fields[] = new CollapsableSubkopje('In te vullen door NovCie', true);

			if ($inschrijven) {
				// Alleen als inschrijven, anders bovenin voor admin
				$fields[] = new JaNeeField(
					'machtiging',
					$profiel->machtiging,
					'Machtiging getekend?'
				);
			}
			$fields[] = new JaNeeField(
				'toestemmingAfschrijven',
				$profiel->toestemmingAfschrijven,
				'Toestemming afschrijven?'
			);

			$fields['novitiaat'] = new TextareaField(
				'novitiaat',
				$profiel->novitiaat,
				'Wat verwacht Noviet van novitiaat?'
			);
			$fields['novitiaat']->required = $inschrijven;
			$fields['novietSoort'] = new SelectField(
				'novietSoort',
				$profiel->novietSoort,
				'Soort Noviet',
				['noviet', 'nanoviet']
			);
			$fields['novietSoort']->required = $inschrijven;
			$fields['matrixPlek'] = new SelectField(
				'matrixPlek',
				$profiel->matrixPlek,
				'Matrix plek',
				['voor', 'midden', 'achter']
			);
			$fields['matrixPlek']->required = $inschrijven;
			$fields['startkamp'] = new SelectField(
				'startkamp',
				$profiel->startkamp,
				'Startkamp',
				['ja', 'nee']
			);
			$fields['startkamp']->required = $inschrijven;

			$fields[] = new TextareaField(
				'novitiaatBijz',
				$profiel->novitiaatBijz,
				'Bijzonderheden novitiaat (op dag x ...)'
			);
			$fields[] = new TextareaField(
				'kgb',
				$profiel->kgb,
				'Overige NovCie-opmerking'
			);

			$fields[] = new Subkopje('Medisch');
			$fields[] = new TextareaField(
				'medisch',
				$profiel->medisch,
				'medisch (NB alleen als relevant voor hele NovCie, bijv. allergieen)'
			);
			$fields[] = new TextField(
				'huisarts',
				$profiel->huisarts,
				'Naam huisarts'
			);
			$fields[] = new TelefoonField(
				'huisartsTelefoon',
				$profiel->huisartsTelefoon,
				'Telefoonnummer'
			);
			$fields[] = new TextField(
				'huisartsPlaats',
				$profiel->huisartsPlaats,
				'Plaats'
			);

			$fields[] = new HtmlComment('</div>');
		}
		$fields[] = new FormDefaultKnoppen('/profiel/' . $profiel->uid);

		$this->addFields($fields);
	}
}
