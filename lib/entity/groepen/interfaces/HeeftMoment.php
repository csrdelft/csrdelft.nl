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
	 * @param DateTimeImmutable $beginMoment
	 */
	public function setBeginMoment(DateTimeImmutable $beginMoment): void;

	/**
	 * @return DateTimeImmutable
	 */
	public function getEindMoment(): DateTimeImmutable;

	/**
	 * @param DateTimeImmutable $eindMoment
	 */
	public function setEindMoment(DateTimeImmutable $eindMoment): void;
}
