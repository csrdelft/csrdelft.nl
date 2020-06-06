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

	public function view() {
		$this->css_classes[] = 'ModalForm';

		echo <<<HTML
<div id="modal" class="modal">
	{$this->getFormTag()}
		<div class="modal-dialog modal-form modal-content {$this->modalBreedte}">
HTML;

		$titel = $this->getTitel();
		if (!empty($titel)) {
			echo <<<HTML
			<div class="modal-header">
				<h5 class="modal-title">{$titel}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
HTML;
		}
		if ($this->showMelding) {
			echo getMelding();
		}
		echo <<<HTML
			<div class="modal-body">
HTML;
		if (isset($this->error)) {
			echo '<span class="error">' . $this->error . '</span>';
		}
		//debugprint($this->getError()); //DEBUG
		foreach ($this->getFields() as $field) {
			$field->view();
		}
		echo <<<HTML
			</div>
			<div class="modal-footer clear">
				{$this->getFormKnoppen()->getHtml()}
			</div>
		</div>
	</form>
	{$this->getScriptTag()}
</div>
HTML;
	}

}
