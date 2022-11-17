<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\interfaces\HeeftAanmeldMoment;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\FormFieldFactory;
use CsrDelft\view\formulier\invoervelden\required\RequiredAutocompleteField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * GroepForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @property Groep $model
 */
class GroepForm extends ModalForm
{
	/**
	 * Aanmaken/Wijzigen
	 * @var AccessAction
	 */
	private $mode;

	/**
	 * GroepForm constructor.
	 * @param Groep $groep
	 * @param $action
	 * @param AccessAction $mode
	 * @param false $nocancel
	 * @throws \Exception
	 */
	public function __construct(Groep $groep, $action, $mode, $nocancel = false)
	{
		parent::__construct(
			$groep,
			$action,
			classNameZonderNamespace(get_class($groep)),
			true
		);
		$this->mode = $mode;
		if ($groep->id) {
			$this->titel .= ' wijzigen';
		} else {
			$this->titel .= ' aanmaken';
		}
		$fields = FormFieldFactory::generateFields($this->model);

		$fields['oudId']->hidden = true;

		$fields['familie'] = new RequiredAutocompleteField(
			'familie',
			$this->model->familie,
			$fields['familie']->description,
			false
		);
		$fields['familie']->title =
			'Vul hier een \'achternaam\' in zodat de juiste ketzers elkaar opvolgen';
		$fields['familie']->suggestions[] = $groep->getFamilieSuggesties();
		$fields['omschrijving']->description = 'Meer lezen';

		if ($groep instanceof HeeftMoment) {
			$fields['beginMoment']->to_datetime = $fields['eindMoment'];
			$fields['eindMoment']->from_datetime = $fields['beginMoment'];
		}

		if ($groep instanceof Activiteit) {
			$fields['eindMoment']->required = true;
		}

		if ($groep instanceof HeeftAanmeldMoment) {
			$fields['aanmeldenVanaf']->to_datetime = $fields['aanmeldenTot'];
			$fields['aanmeldenTot']->from_datetime = $fields['aanmeldenVanaf'];

			if ($groep instanceof HeeftMoment) {
				$fields['beginMoment']->title =
					'Dit is NIET het moment van openstellen voor aanmeldingen';
				$fields['eindMoment']->title =
					'Dit is NIET het moment van sluiten voor aanmeldingen';
			}
		}

		if ($groep instanceof Ketzer || $groep instanceof Activiteit) {
			$fields['bewerkenTot']->title =
				'Leden mogen hun eigen opmerking of keuze niet aanpassen als u dit veld leeg laat';
			$fields['afmeldenTot']->title =
				'Leden mogen zichzelf niet afmelden als u dit veld leeg laat';
			$fields['keuzelijst']->title =
				'Zet | tussen de opties en gebruik && voor meerdere keuzelijsten';
		}
		if ($groep instanceof Kring) {
			unset($fields['samenvatting']);
		}

		// GROEPEN_V2
		if (!LoginService::mag(P_ADMIN)) {
			$fields['versie']->hidden = true;
			$fields['keuzelijst2']->hidden = true;
		} else {
			$fields['versie']->title =
				'Versie 2 is een testversie, niet gebruiken als je niet weet wat je doet.';
			$fields['keuzelijst2']->title =
				'Formaat: naam:type:default:description:optie,optie,optie|naam:type:default:description:|...';
		}

		$fields['maker']->readonly = !LoginService::mag(P_ADMIN);
		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen($nocancel ? false : null);
	}

	public function validate()
	{
		/**
		 * @var Groep $groep
		 */
		$groep = $this->getModel();
		if ($groep instanceof HeeftSoort) {
			$soort = $groep->getSoort();
		} else {
			$soort = null;
		}
		/**
		 * @Notice: Similar function in GroepSoortField->validate()
		 */
		if (!$groep->magAlgemeen($this->mode, $soort)) {
			if (!$groep->mag($this->mode)) {
				// beide aanroepen vanwege niet doorsturen van param $soort door mag() naar magAlgemeen()
				if ($soort) {
					$naam = $soort->getDescription();
				} else {
					$naam = classNameZonderNamespace(get_class($groep));
				}
				setMelding('U mag geen ' . $naam . ' aanmaken', -1);
				return false;
			} /**
			 * Omdat wijzigen wel is toegestaan met hetzelfde formulier
			 * en groep->mag() @runtime niet weet wat de orig value was (door form auto property set)
			 * op moment van uitvoeren van deze funtie, hier een extra check:
			 *
			 * N.B.: Deze check staat binnen de !magAlgemeen zodat P_LEDEN_MOD deze check overslaat
			 */ elseif (
				AccessAction::isWijzigen($this->mode) &&
				$groep instanceof Woonoord
			) {
				$vorigeHuisStatus = HuisStatus::from(
					$this->findByName('huisStatus')->getOrigValue()
				);
				if ($vorigeHuisStatus !== $soort) {
					setMelding('U mag de huisstatus niet wijzigen', -1);
					return false;
				}
			}
		}

		$fields = $this->getFields();
		if (
			isset($fields['eindMoment']) &&
				$fields['eindMoment']->getValue() !== null and
			strtotime($fields['eindMoment']->getValue()) <
				strtotime($fields['beginMoment']->getValue())
		) {
			$fields['eindMoment']->error = 'Eindmoment moet na beginmoment liggen';
		}
		if ($groep instanceof Ketzer) {
			if (
				$fields['afmeldenTot']->getValue() !== null and
				strtotime($fields['afmeldenTot']->getValue()) <
					strtotime($fields['aanmeldenVanaf']->getValue())
			) {
				$fields['afmeldenTot']->error =
					'Afmeldperiode moet eindigen na begin aanmeldperiode';
			}
			if (
				$fields['bewerkenTot']->getValue() !== null and
				strtotime($fields['bewerkenTot']->getValue()) <
					strtotime($fields['aanmeldenVanaf']->getValue())
			) {
				$fields['bewerkenTot']->error =
					'Bewerkenperiode moet eindigen na begin aanmeldperiode';
			}
		}

		// GROEPEN_V2
		//		if ($fields['keuzelijst2']->getValue() !== null && $fields['versie']->getValue() === GroepVersie::V2) {
		//			$this->model->keuzelijst2 = $this->parseKeuzelijst($fields['keuzelijst2']->getValue());
		//		}

		return parent::validate();
	}
}
