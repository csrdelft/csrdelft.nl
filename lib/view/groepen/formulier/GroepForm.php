<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\model\entity\groepen\GroepKeuze;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\FormFieldFactory;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * GroepForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GroepForm extends ModalForm {

	/**
	 * Aanmaken/Wijzigen
	 * @var AccessAction
	 */
	private $mode;

	public function __construct(AbstractGroep $groep, $action, $mode, $nocancel = false) {
		parent::__construct($groep, $action, classNameZonderNamespace(get_class($groep)), true);
		$this->mode = $mode;
		if ($groep->id) {
			$this->titel .= ' wijzigen';
		} else {
			$this->titel .= ' aanmaken';
		}
		$fields = FormFieldFactory::generateFields($this->model);

		$fields['familie']->title = 'Vul hier een \'achternaam\' in zodat de juiste ketzers elkaar opvolgen';
		$fields['familie']->suggestions[] = $groep->getFamilieSuggesties();
		$fields['omschrijving']->description = 'Meer lezen';

		$fields['begin_moment']->to_datetime = $fields['eind_moment'];
		$fields['eind_moment']->from_datetime = $fields['begin_moment'];

		if ($groep instanceof Activiteit) {
			$fields['eind_moment']->required = true;
		}
		if ($groep instanceof Ketzer) {
			$fields['begin_moment']->title = 'Dit is NIET het moment van openstellen voor aanmeldingen';
			$fields['eind_moment']->title = 'Dit is NIET het moment van sluiten voor aanmeldingen';
			$fields['aanmelden_vanaf']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->to_datetime = $fields['afmelden_tot'];
			$fields['bewerken_tot']->from_datetime = $fields['aanmelden_vanaf'];
			$fields['bewerken_tot']->title = 'Leden mogen hun eigen opmerking of keuze niet aanpassen als u dit veld leeg laat';
			$fields['afmelden_tot']->from_datetime = $fields['aanmelden_vanaf'];
			$fields['afmelden_tot']->title = 'Leden mogen zichzelf niet afmelden als u dit veld leeg laat';
			$fields['keuzelijst']->title = 'Zet | tussen de opties en gebruik && voor meerdere keuzelijsten';
		}
		if ($groep instanceof Kring) {
			unset($fields['samenvatting']);
		}

		// GROEPEN_V2
		if (!LoginService::mag(P_ADMIN)) {
			$fields['versie']->hidden = true;
			$fields['keuzelijst2']->hidden = true;
		} else {
			$fields['versie']->title = 'Versie 2 is een testversie, niet gebruiken als je niet weet wat je doet.';
			$fields['keuzelijst2']->title = 'Formaat: naam:type:default:description:optie,optie,optie|naam:type:default:description:|...';
		}

		$fields['maker']->readonly = !LoginService::mag(P_ADMIN);
		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen($nocancel ? false : null);
	}

	public function validate() {
		/**
		 * @var AbstractGroep $groep
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
		if (!$groep->magAlgemeen($this->mode, null, $soort)) {
			if (!$groep->mag($this->mode)) {
				// beide aanroepen vanwege niet doorsturen van param $soort door mag() naar magAlgemeen()
				if ($groep instanceof Activiteit) {
					$naam = ActiviteitSoort::from($soort)->getDescription();
				} elseif ($groep instanceof Commissie) {
					$naam = CommissieSoort::from($soort)->getDescription();
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
			 */
			elseif ($this->mode === AccessAction::Wijzigen and $groep instanceof Woonoord) {

				$origvalue = $this->findByName('soort')->getOrigValue();
				if ($origvalue !== $soort) {
					setMelding('U mag de huisstatus niet wijzigen', -1);
					return false;
				}
			}
		}

		$fields = $this->getFields();
		if ($fields['eind_moment']->getValue() !== null and strtotime($fields['eind_moment']->getValue()) < strtotime($fields['begin_moment']->getValue())) {
			$fields['eind_moment']->error = 'Eindmoment moet na beginmoment liggen';
		}
		if ($groep instanceof Ketzer) {
			if ($fields['afmelden_tot']->getValue() !== null and strtotime($fields['afmelden_tot']->getValue()) < strtotime($fields['aanmelden_vanaf']->getValue())) {
				$fields['afmelden_tot']->error = 'Afmeldperiode moet eindigen na begin aanmeldperiode';
			}
			if ($fields['bewerken_tot']->getValue() !== null and strtotime($fields['bewerken_tot']->getValue()) < strtotime($fields['aanmelden_vanaf']->getValue())) {
				$fields['bewerken_tot']->error = 'Bewerkenperiode moet eindigen na begin aanmeldperiode';
			}
		}

		// GROEPEN_V2
		if ($fields['keuzelijst2']->getValue() !== null && $fields['versie']->getValue() === GroepVersie::V2) {
			$this->model->keuzelijst2 = $this->parseKeuzelijst($fields['keuzelijst2']->getValue());
		}

		return parent::validate();
	}

	private function parseKeuzelijst($keuzelijst) {
		$return = [];
		$keuzes = explode('|', $keuzelijst);
		foreach ($keuzes as $keuze) {
			$attrs = explode(':', $keuze);
			$opties = explode(',', $attrs[4]);
			$groepKeuze = new GroepKeuze($attrs[0], $attrs[1], $attrs[2], $attrs[3]);
			$groepKeuze->opties = $opties;
			$return[] = $groepKeuze;
		}

		return $return;
	}
}
