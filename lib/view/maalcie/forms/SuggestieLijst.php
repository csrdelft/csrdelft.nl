<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\corvee\CorveePuntenOverzichtDTO;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\ToResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SuggestieLijst implements ToResponse, FormElement
{
	/** @var bool  */
	private $voorkeurbaar;
	/** @var string  */
	private $voorkeur;
	/** @var string  */
	private $recent;

	public function __construct(
		/**
		 * @var CorveePuntenOverzichtDTO[]
		 */
		private readonly array $suggesties,
		private readonly Environment $twig,
		private readonly CorveeTaak $taak
	) {
		if ($this->taak->corveeRepetitie !== null) {
			$this->voorkeurbaar = $this->taak->corveeRepetitie->voorkeurbaar;
		}

		if ($this->taak->corveeFunctie->kwalificatie_benodigd) {
			$this->voorkeur = InstellingUtil::instelling(
				'corvee',
				'suggesties_voorkeur_kwali_filter'
			);
			$this->recent = InstellingUtil::instelling(
				'corvee',
				'suggesties_recent_kwali_filter'
			);
		} else {
			$this->voorkeur = InstellingUtil::instelling(
				'corvee',
				'suggesties_voorkeur_filter'
			);
			$this->recent = InstellingUtil::instelling(
				'corvee',
				'suggesties_recent_filter'
			);
		}
	}

	public function getHtml()
	{
		return $this->twig->render(
			'maaltijden/corveetaak/suggesties_lijst.html.twig',
			[
				'suggesties' => $this->suggesties,
				'jongsteLichting' => LichtingenRepository::getJongsteLidjaar(),
				'voorkeur' => $this->voorkeur,
				'recent' => $this->recent,
				'voorkeurbaar' => $this->voorkeurbaar,
				'kwalificatie_benodigd' =>
					$this->taak->corveeFunctie->kwalificatie_benodigd,
			]
		);
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}

	public function getTitel()
	{
		return $this->getType();
	}

	public function getType()
	{
		return static::class;
	}

	public function getJavascript()
	{
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

	public function getModel()
	{
		return $this->suggesties;
	}

	public function toResponse(): Response
	{
		return new Response($this->getHtml());
	}

	public function getBreadcrumbs()
	{
		return '';
	}
}
