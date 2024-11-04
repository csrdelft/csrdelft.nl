<?php

namespace CsrDelft\entity\groepen\interfaces;

use DateTimeImmutable;

interface HeeftAanmeldMoment
{
	/**
	 * @return DateTimeImmutable
	 */
	public function getAanmeldenVanaf(): DateTimeImmutable;

	/**
	 * @return DateTimeImmutable
	 */
	public function getAanmeldenTot(): ?DateTimeImmutable;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getBewerkenTot(): ?DateTimeImmutable;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getAfmeldenTot(): ?DateTimeImmutable;
}
