<?php
/**
 * JsonLijstResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */

namespace CsrDelft\view;

use CsrDelft\common\Util\ArrayUtil;

abstract class JsonLijstResponse extends JsonResponse
{
	public function getModel(): array
	{
		return array_map(function ($element) {
			return $this->renderElement($element);
		}, ArrayUtil::as_array($this->model));
	}

	abstract public function renderElement($element);
}
