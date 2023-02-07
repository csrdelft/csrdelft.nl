<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\formulier\knoppen\ModalCloseButtons;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\groepen\GroepView;
use Twig\Environment;

class GroepPreviewForm extends ModalForm implements FormElement
{
	public function __construct(Environment $twig, Groep $groep)
	{
		parent::__construct($groep, null, 'Voorbeeldweergave');

		$fields = [];
		$fields[] = new HtmlBbComment(
			'<div style="max-width: 580px;">Gebruik de volgende code in uw forumbericht voor onderstaand resultaat: [code][' .
				strtolower(
					ReflectionUtil::classNameZonderNamespace(get_class($groep))
				) .
				'=' .
				$groep->id .
				'][/code][rn]'
		);
		$fields[] = new GroepView($twig, $groep, null, false, true);
		$fields[] = new HtmlComment('</div>');

		$this->addFields($fields);

		$this->formKnoppen = new ModalCloseButtons();
	}

	public function getHtml()
	{
		$this->css_classes[] = 'ModalForm';
		$html = MeldingUtil::getMelding();
		$html .= $this->getFormTag();
		if ($this->getTitel()) {
			$html .= '<h1 class="Titel">' . $this->getTitel() . '</h1>';
		}
		foreach ($this->getFields() as $field) {
			$html .= $field->getHtml();
		}
		$html .= $this->getScriptTag();
		return $html . '</form>';
	}

	public function getJavascript()
	{
		parent::getJavascript();
	}

	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace(get_class($this->model));
	}
}
