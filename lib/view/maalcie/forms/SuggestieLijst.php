<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;

class SuggestieLijst implements ToResponse, FormElement {

	private $suggesties;
	private $taak;
	private $voorkeurbaar;
	private $voorkeur;
	private $recent;

	public function __construct(
		array $suggesties,
		CorveeTaak $taak
	) {
		$this->suggesties = $suggesties;
		$this->taak = $taak;

		$crid = $taak->crv_repetitie_id;
		if ($crid !== null) {
			$this->voorkeurbaar = CorveeRepetitiesModel::instance()->getRepetitie($crid)->voorkeurbaar;
		}

		if ($taak->getCorveeFunctie()->kwalificatie_benodigd) {
			$this->voorkeur = instelling('corvee', 'suggesties_voorkeur_kwali_filter');
			$this->recent = instelling('corvee', 'suggesties_recent_kwali_filter');
		} else {
			$this->voorkeur = instelling('corvee', 'suggesties_voorkeur_filter');
			$this->recent = instelling('corvee', 'suggesties_recent_filter');
		}
	}

	public function getHtml() {
		return view('maaltijden.corveetaak.suggesties_lijst', [
			'suggesties' => $this->suggesties,
			'jongsteLichting' => LichtingenModel::getJongsteLidjaar(),
			'voorkeur' => $this->voorkeur,
			'recent' => $this->recent,
			'voorkeurbaar' => $this->voorkeurbaar,
			'kwalificatie_benodigd' => $this->taak->getCorveeFunctie()->kwalificatie_benodigd,
		])->getHtml();
	}

	public function view() {
		echo $this->getHtml();
	}

	public function getTitel() {
		return $this->getType();
	}

	public function getType() {
		return get_class($this);
	}

	public function getJavascript() {
		$js = <<<JS

/* {$this->getTitel()} */
window.maalcie.takenColorSuggesties();

JS;
		if (isset($this->voorkeurbaar) and $this->voorkeur) {
			$js .= "window.maalcie.takenToggleSuggestie('geenvoorkeur');";
		}
		if ($this->recent) {
			$js .= "window.maalcie.takenToggleSuggestie('recent');";
		}
		return $js;
	}

	public function getModel() {
		return $this->suggesties;
	}

	public function toResponse(): Response {
		return new Response($this->getHtml());
	}

	public function getBreadcrumbs() {
		return '';
	}
}
