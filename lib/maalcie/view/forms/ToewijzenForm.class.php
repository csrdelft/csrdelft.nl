<?php

/**
 * ToewijzenForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 * 
 */
class ToewijzenForm extends PopupForm {

	public function __construct(CorveeTaak $taak, array $suggesties) {
		parent::__construct(null, 'maalcie-taak-toewijzen-form', Instellingen::get('taken', 'url') . '/toewijzen/' . $taak->getTaakId());

		if (!is_int($taak->getTaakId()) || $taak->getTaakId() <= 0) {
			throw new Exception('invalid tid');
		}
		$this->titel = 'Taak toewijzen aan lid';
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('uid', $taak->getUid(), 'Naam of lidnummer', 'leden');
		$fields[] = new SuggestieLIjst($suggesties, $taak);
		$fields[] = new FormButtons();

		$this->addFields($fields);
	}

}

class SuggestieLijst extends SmartyTemplateView implements FormElement {

	private $jongste_lichting;
	private $voorkeurbaar;
	private $voorkeur;
	private $recent;

	public function __construct(array $suggesties, CorveeTaak $taak) {
		parent::__construct($suggesties);

		$this->jongste_lichting = Lichting::getJongsteLichting();
		$this->smarty->assign('suggesties', $this->model);

		$crid = $taak->getCorveeRepetitieId();
		if ($crid !== null) {
			$this->voorkeurbaar = CorveeRepetitiesModel::getRepetitie($crid)->getIsVoorkeurbaar();
			$this->smarty->assign('voorkeurbaar', $this->voorkeurbaar);
		}
		$this->smarty->assign('kwalificatie_benodigd', $taak->getCorveeFunctie()->kwalificatie_benodigd);

		if ($taak->getCorveeFunctie()->kwalificatie_benodigd) {
			$this->voorkeur = Instellingen::get('corvee', 'suggesties_voorkeur_kwali_filter');
			$this->recent = Instellingen::get('corvee', 'suggesties_recent_kwali_filter');
		} else {
			$this->voorkeur = Instellingen::get('corvee', 'suggesties_voorkeur_filter');
			$this->recent = Instellingen::get('corvee', 'suggesties_recent_filter');
		}
		$this->smarty->assign('voorkeur', $this->voorkeur);
		$this->smarty->assign('recent', $this->recent);
	}

	public function view() {
		$this->smarty->display('maalcie/corveetaak/suggesties_lijst.tpl');
	}

	public function getType() {
		return get_class($this);
	}

	public function getJavascript() {
		$js = '$("#suggesties-tabel").show(1, taken_color_suggesties); ';
		if (isset($this->voorkeurbaar) and $this->voorkeur) {
			$js .= 'taken_toggle_suggestie("geenvoorkeur"); ';
		}
		if ($this->recent) {
			$js .= 'taken_toggle_suggestie("recent"); ';
		}
		return $js;
	}

	public function getIsJongsteLichting($uid) {
		return ($this->jongste_lichting === LidCache::getLid($uid)->getLichting());
	}

}
