<?php

namespace CsrDelft\view\formulier;

/**
 * Form as modal content.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 06/05/2017
 */
class ModalForm extends Formulier {
	/**
	 * Kan modal-lg (breed), modal-sm (smal) of leeg (normaal) zijn.
	 *
	 * @var string
	 */
	protected $modalBreedte = '';

	public function __toString() {
		$this->css_classes[] = 'ModalForm';
		$html = '';

		$html .= <<<HTML
<div id="modal" class="modal">
	{$this->getFormTag()}
		<div class="modal-dialog modal-form modal-content {$this->modalBreedte}">
HTML;

		$titel = $this->getTitel();
		if (!empty($titel)) {
			$html .= <<<HTML
			<div class="modal-header">
				<h5 class="modal-title">{$titel}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
HTML;
		}
		if ($this->showMelding) {
			$html .= getMelding();
		}
		$html .= <<<HTML
			<div class="modal-body">
HTML;
		if (isset($this->error)) {
			$html .= '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->getFields() as $field) {
			$html .= $field->__toString();
		}
		$html .= <<<HTML
			</div>
			<div class="modal-footer clear">
				{$this->getFormKnoppen()->getHtml()}
			</div>
		</div>
	</form>
	{$this->getScriptTag()}
</div>
HTML;
		return $html;
	}

}
