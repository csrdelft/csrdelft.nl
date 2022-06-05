<?php
/**
 * GroepOmschrijvingView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;

class GroepOmschrijvingView implements FormElement, ToResponse
{
	use ToHtmlResponse;

	protected $groep;
	protected $javascript;

	public function __construct(Groep $groep)
	{
		$this->groep = $groep;
		$this->javascript = '';
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getType()
	{
		return classNameZonderNamespace(get_class($this));
	}

	public function getModel()
	{
		return $this->groep;
	}

	public function getTitel()
	{
		return $this->groep->naam;
	}

	public function getJavascript()
	{
		return $this->javascript;
	}

	public function getHtml()
	{
		$this->javascript .= <<<JS

$('#groep-omschrijving-{$this->groep->id}').hide().slideDown(600);
JS;
		return '<div id="groep-omschrijving-' .
			$this->groep->id .
			'">' .
			CsrBB::parse($this->groep->omschrijving) .
			$this->getScriptTag() .
			'</div>';
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	protected function getScriptTag()
	{
		return <<<JS
<script type="text/javascript">
$(document).ready(function () {
	{$this->getJavascript()}
});
</script>
JS;
	}
}
