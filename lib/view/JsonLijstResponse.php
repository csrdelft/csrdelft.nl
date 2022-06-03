<?php
/**
 * JsonLijstResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view;

abstract class JsonLijstResponse extends JsonResponse
{

	public function getModel()
	{
		return array_map(function ($element) {
			return $this->renderElement($element);
		}, as_array($this->model));
	}

	public abstract function renderElement($element);
}
