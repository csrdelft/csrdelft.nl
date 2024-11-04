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
	 * @param DateTimeImmutable $aanmeldenTot
	 */
	public function setAanmeldenTot(DateTimeImmutable $aanmeldenTot): void;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getBewerkenTot(): ?DateTimeImmutable;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getAfmeldenTot(): ?DateTimeImmutable;
}
