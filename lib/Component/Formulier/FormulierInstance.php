<?php

namespace CsrDelft\Component\Formulier;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\ChangeLogEntry;
use CsrDelft\repository\ChangeLogRepository;
use CsrDelft\service\CsrfService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\Validator;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-22
 * @see FormulierBuilder
 */
class FormulierInstance
{
	public $error;
	protected $enctype = 'multipart/form-data';
	/**
	 * @var string
	 */
	private $formId;
	private $model;
	private $modalBreedte;

	/**
	 * @param FormElement[] $fields
	 */
	public function __construct(
		private readonly Environment $twig,
		private $action,
		private $titel,
		private $dataTableId,
		private $formKnoppen,
		/** @var FormElement[] */
		private $fields,
		private $showMelding,
		private $preventCsrf,
		private $css_classes,
		private $validationMethods = [],
		public $post = true
	) {
		$this->formId = CryptoUtil::uniqid_safe('Formulier_');
	}

	public function createView()
	{
		$html = '';
		if ($this->showMelding) {
			$html .= $this->twig->render('melding.html.twig');
		}
		$html .= $this->getFormTag();
		$titel = $this->titel;
		if (!empty($titel)) {
			$html .= '<h1 class="Titel">' . $titel . '</h1>';
		}
		if (property_exists($this, 'error') && $this->error !== null) {
			$html .= '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->fields as $field) {
			$html .= $field->__toString();
		}
		$csrfField = $this->getCsrfField();
		if ($csrfField != null) {
			$html .= $csrfField->getHtml();
		}
		$html .= $this->formKnoppen->getHtml();
		$html .= $this->getScriptTag();
		$html .= '</form>';

		return new FormulierView($html, $this->titel);
	}

	protected function getFormTag()
	{
		if ($this->dataTableId) {
			$this->css_classes[] = 'DataTableResponse';
		}
		return '<form enctype="' .
			$this->enctype .
			'" action="' .
			htmlspecialchars((string) $this->action) .
			'" id="' .
			$this->formId .
			'" data-tableid="' .
			$this->dataTableId .
			'" class="' .
			implode(' ', $this->css_classes) .
			'" method="' .
			($this->post ? 'post' : 'get') .
			'">';
	}

	public function getCsrfField()
	{
		if (!$this->preventCsrf) {
			return null;
		}
		$csrfService = ContainerFacade::getContainer()->get(CsrfService::class);
		$token = $csrfService->generateToken($this->action, $this->getMethod());
		return new CsrfField($token);
	}

	public function getMethod()
	{
		return $this->post ? 'post' : 'get';
	}

	protected function getScriptTag()
	{
		$js = $this->getJavascript();
		if (trim((string) $js) === '') {
			return '';
		}
		return <<<HTML
<script type="text/javascript">
docReady(function() {
	var form = document.getElementById('{$this->formId}');
	{$js}
});
</script>
HTML;
	}

	protected function getJavascript()
	{
		$javascript = '';
		foreach ($this->fields as $field) {
			$javascript .= $field->getJavascript();
		}
		return $javascript;
	}

	public function createModalView()
	{
		$html = '';
		$this->css_classes[] = 'ModalForm';

		$html .= <<<HTML
<div id="modal" class="modal">
	{$this->getFormTag()}
		<div class="modal-dialog modal-form modal-content {$this->modalBreedte}">
HTML;

		$titel = $this->titel;
		if (!empty($titel)) {
			$html .= <<<HTML
			<div class="modal-header">
				<h5 class="modal-title">{$titel}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
HTML;
		}
		if ($this->showMelding) {
			$html .= $this->twig->render('melding.html.twig');
		}
		$html .= <<<HTML
			<div class="modal-body">
HTML;
		if (property_exists($this, 'error') && $this->error !== null) {
			$html .= '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->fields as $field) {
			$html .= $field->__toString();
		}
		$html .= <<<HTML
			</div>
			<div class="modal-footer clear">
				{$this->formKnoppen->getHtml()}
			</div>
		</div>
	</form>
	{$this->getScriptTag()}
</div>
HTML;
		return new FormulierView($html, $this->titel);
	}

	/**
	 * Geeft waardes van de formuliervelden terug.
	 */
	public function getValues()
	{
		$values = [];
		foreach ($this->fields as $field) {
			if ($field instanceof InputField) {
				$values[$field->getName()] = $field->getValue();
			}
		}
		return $values;
	}

	/**
	 * Geeft errors van de formuliervelden terug.
	 */
	public function getError()
	{
		$errors = [];
		foreach ($this->fields as $field) {
			if ($field instanceof Validator) {
				$fieldName = $field->getName();
				if ($field->getError() !== '') {
					$errors[$fieldName] = $field->getError();
				}
			}
		}
		if ($errors === []) {
			return null;
		}
		return $errors;
	}

	/**
	 * Alle valideer-functies kunnen het model gebruiken bij het valideren
	 * dat meegegeven is bij de constructie van het InputField.
	 */
	public function validate()
	{
		if (!$this->isPosted()) {
			return false;
		}
		$valid = true;
		foreach ($this->fields as $field) {
			if ($field instanceof Validator && !$field->validate()) {
				// geen comments bijv.
				$valid = false; // niet gelijk retourneren om voor alle velden eventueel errors te zetten
			}
		}

		foreach ($this->validationMethods as $validationMethod) {
			if (!$validationMethod($this->fields)) {
				$valid = false;
			}
		}

		if (!$valid) {
			$this->css_classes[] = 'metFouten';
		}
		return $valid;
	}

	/**
	 * Is het formulier *helemaal* gePOST?
	 */
	public function isPosted()
	{
		foreach ($this->fields as $field) {
			if ($field instanceof InputField && !$field->isPosted()) {
				//MeldingUtil::setMelding($field->getName() . ' is niet gepost', 2); //DEBUG
				return false;
			}
		}
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
	 * Geef een array terug van de gewijzigde velden.
	 *
	 * @returns ChangeLogEntry[]
	 */
	public function diff()
	{
		$changeLogRepository = ContainerFacade::getContainer()->get(
			ChangeLogRepository::class
		);
		$diff = [];
		foreach ($this->fields as $field) {
			if ($field instanceof InputField) {
				$old = $field->getOrigValue();
				$new = $field->getValue();
				if ($old !== $new) {
					$prop = $field->getName();
					$diff[$prop] = $changeLogRepository->nieuw(
						$this->model,
						$prop,
						$old,
						$new
					);
				}
			}
		}
		return $diff;
	}

	/**
	 * Maak een stukje bbcode aan met daarin de huidige wijziging, door wie en wanneer.
	 *
	 * @param ChangeLogEntry[] $diff
	 * @return string
	 */
	public function changelog(array $diff)
	{
		$changelog = '';
		if ($diff !== []) {
			$changelog .=
				'[div]Bewerking van [lid=' .
				LoginService::getUid() .
				'] op [reldate]' .
				DateUtil::getDatetime() .
				'[/reldate][br]';
			foreach ($diff as $change) {
				$changelog .=
					'(' .
					$change->property .
					') ' .
					$change->old_value .
					' => ' .
					$change->new_value .
					'[br]';
			}
			$changelog .= '[/div][hr]';
		}
		return $changelog;
	}

	public function handleRequest(Request $request)
	{
		if ($this->isPosted()) {
			foreach ($this->fields as $field) {
				if ($field instanceof InputField) {
					$this->loadProperty($field);
				}
			}
		}
	}

	private function loadProperty(InputField $field)
	{
		$fieldName = $field->getName();
		if ($this->model) {
			if (method_exists($this->model, 'set' . ucfirst((string) $fieldName))) {
				call_user_func(
					[$this->model, 'set' . ucfirst((string) $fieldName)],
					$field->getFormattedValue()
				);
			} elseif (property_exists($this->model, $fieldName)) {
				$this->model->$fieldName = $field->getFormattedValue();
			}
		}
	}

	public function setModel(mixed $model): void
	{
		$this->model = $model;
	}

	public function getField($name)
	{
		return $this->fields[$name];
	}
}
