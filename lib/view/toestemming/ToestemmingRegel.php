<?php

namespace CsrDelft\view\toestemming;

class ToestemmingRegel
{
	/**
	 * @param string $module
	 * @param string $id
	 * @param string $type
	 * @param string $opties
	 * @param string $label
	 * @param string $waarde
	 * @param string $default
	 */
	public function __construct(
		public $module,
		public $id,
		public $type,
		public $opties,
		public $label,
		public $waarde,
		public $default
	) {
	}
}
