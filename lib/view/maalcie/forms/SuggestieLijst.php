<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\corvee\CorveePuntenOverzichtDTO;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SuggestieLijst implements ToResponse, FormElement {

	/**
	 * @var CorveePuntenOverzichtDTO[]
	 */
	private $suggesties;
	private $taak;
	private $voorkeurbaar;
	private $voorkeur;
	private $recent;
	/**
	 * @var Environment
	 */
	private $twig;

	public function __construct(
		array $suggesties,
		Environment $twig,
		CorveeTaak $taak
	) {
		$this->suggesties = $suggesties;
		$this->taak = $taak;

		if ($taak->corveeRepetitie !== null) {
			$this->voorkeurbaar = $taak->corveeRepetitie->voorkeurbaar;
		}

		if ($taak->corveeFunctie->kwalificatie_benodigd) {
			$this->voorkeur = instelling('corvee', 'suggesties_voorkeur_kwali_filter');
			$this->recent = instelling('corvee', 'suggesties_recent_kwali_filter');
		} else {
			$this->voorkeur = instelling('corvee', 'suggesties_voorkeur_filter');
			$this->recent = instelling('corvee', 'suggesties_recent_filter');
		}
		$this->twig = $twig;
	}

	public function getHtml() {
		return $this->twig->render('maaltijden/corveetaak/suggesties_lijst.html.twig', [
			'suggesties' => $this->suggesties,
			'jongsteLichting' => LichtingenRepository::getJongsteLidjaar(),
			'voorkeur' => $this->voorkeur,
			'recent' => $this->recent,
			'voorkeurbaar' => $this->voorkeurbaar,
			'kwalificatie_benodigd' => $this->taak->corveeFunctie->kwalificatie_benodigd,
		]);
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
