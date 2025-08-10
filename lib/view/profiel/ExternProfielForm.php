<?php

namespace CsrDelft\view\profiel;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\Geslacht;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\getalvelden\TelefoonField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\IBANField;
use CsrDelft\view\formulier\invoervelden\LandField;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLandField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\StudieField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateObjectField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredEnumSelectField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Exception;

/**
 * @property ProfielRepository $model
 */
class ExternProfielForm extends Formulier
{
	public function __construct(Profiel $profiel, $url)
	{
		parent::__construct($profiel, $url);

		$fields = [];
		$this->titel =
			'Welkom bij C.S.R. Delft, ' . htmlentities($profiel->voornaam) . '!';
		$fields[] = new HtmlComment('<p>
			Hieronder mag je gegevens invullen in het databeest van de Civitas. Zo kunnen we contact met je houden,
			kunnen andere leden opzoeken waar je woont en kun je (na het novitiaat) op het forum berichten plaatsen.
		</p>');

		$fields[] = new Subkopje('Identiteit');
		$fields[] = new RequiredTextField(
			'voornaam',
			$profiel->voornaam,
			'Roepnaam',
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

		$fields[] = new RequiredDateObjectField(
			'gebdatum',
			date_create_immutable_from_format('Ymd', '20020101'),
			'Geboortedatum',
			date('Y') - 15,
			1900
		);

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

		$fields[] = new Subkopje('Contact');
		$fields['email'] = new RequiredEmailField(
			'email',
			$profiel->email,
			'E-mailadres'
		);

		// Mobiel & telefoon
		$fields['mobiel'] = new TelefoonField(
			'mobiel',
			$profiel->mobiel,
			'Mobiel',
			20
		);
		$fields['mobiel']->required = true;
		$fields[] = new TelefoonField(
			'telefoon',
			$profiel->telefoon,
			'Telefoonnummer (vast)',
			20
		);

		$fields[] = new Subkopje('Boekhouding');

		// Bankrekeningnummer
		$fields['bankrekening'] = new IBANField(
			'bankrekening',
			$profiel->bankrekening,
			'Bankrekening',
			34
		);
		$fields['bankrekening']->required = true;

		$fields[] = new HtmlComment('<p>
			Door dit vakje aan te vinken geef je de fiscus van C.S.R. toestemming om bedragen van je rekening af te schrijven voor contributie en activiteiten. Zowel voor jou als voor de fiscus is dit erg fijn. De contributie komend jaar is vastgesteld op &euro; 147,50 euro.
		</p>');
		$fields[] = new JaNeeField(
			'toestemmingAfschrijven',
			$profiel->toestemmingAfschrijven,
			'Ik geef C.S.R. toestemming voor afschrijven voor contributie en activiteiten'
		);

		$fields[] = new Subkopje('Studie');
		$fields['studie'] = new StudieField(
			'studie',
			$profiel->studie,
			'Onderwijsinstelling en studie'
		);
		$fields['studie']->required = true;
		$fields['studiejaar'] = new IntField(
			'studiejaar',
			(int) $profiel->lidjaar,
			'Beginjaar studie',
			1950,
			date('Y')
		);
		$fields['studiejaar']->required = true;

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
		$fields[] = new RequiredSelectField('zingen', $profiel->zingen, 'Zingen', [
			'' => 'Kies...',
			'ja' => 'Ja, ik zing in een band/koor',
			'nee' => 'Nee, ik houd niet van zingen',
			'soms' => 'Alleen onder de douche',
			'anders' => 'Anders',
		]);

		$fields[] = new TextField(
			'vrienden',
			$profiel->vrienden,
			'Vrienden binnen C.S.R.',
			300
		);
		$fields['middelbareSchool'] = new TextField(
			'middelbareSchool',
			$profiel->middelbareSchool,
			'Middelbare school',
			200
		);
		$fields['middelbareSchool']->required = true;

		$fields[] = new Subkopje('Medisch');
		$fields[] = new HtmlComment('<p>
			Voor eventuele noodgevallen tijdens de novitiaatsweek willen we graag weten wie jouw huisarts is.
			Vanwege de vertrouwelijkheid vragen we je niet om in dit formulier verdere medische gegevens in te vullen.
			Tijdens het telefoongesprek dat je na het invullen van dit formulier met een lid van de novitiaatscommissie zult hebben,
			zal er wel een aantal vragen gesteld worden met betrekking tot je gezondheid die van belang kunnen zijn tijdens de novitiaatsweek.
		</p>');
		$fields[] = new RequiredTextField(
			'huisarts',
			$profiel->huisarts,
			'Naam huisarts'
		);
		$fields['huisarts_telefoon'] = new TelefoonField(
			'huisartsTelefoon',
			$profiel->huisartsTelefoon,
			'Telefoonnummer'
		);
		$fields['huisarts_telefoon']->required = true;
		$fields[] = new RequiredTextField(
			'huisartsPlaats',
			$profiel->huisartsPlaats,
			'Plaats'
		);

		// Zorg ervoor dat toestemming bij inschrijven wordt opgegeven.
		$fields[] = new Subkopje('Privacy');
		$fields[] = new HiddenField('toestemming_geven', 'true');
		$lidToestemmingRepository = ContainerFacade::getContainer()->get(
			LidToestemmingRepository::class
		);
		try {
			$fields = array_merge(
				$fields,
				(new ToestemmingModalForm($lidToestemmingRepository, true))->getFields()
			);
		} catch (Exception) {
		}

		$fields[] = new SubmitKnop(
			null,
			'submit',
			'Inschrijving afronden',
			'Inschrijving afronden',
			false
		);

		$this->addFields($fields);
	}
}
