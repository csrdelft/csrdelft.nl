<?php

namespace CsrDelft\entity\groepen\interfaces;

use DateTimeImmutable;

interface HeeftMoment
{
	/**
	 * @return DateTimeImmutable
	 */
	public function getBeginMoment(): DateTimeImmutable;

	/**
	 * @return DateTimeImmutable
	 */
	public function getEindMoment(): DateTimeImmutable;
}
