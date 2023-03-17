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
	 * @param DateTimeImmutable $aanmeldenVanaf
	 */
	public function setAanmeldenVanaf(DateTimeImmutable $aanmeldenVanaf): void;

	/**
	 * @return DateTimeImmutable
	 */
	public function getAanmeldenTot(): DateTimeImmutable;

	/**
	 * @param DateTimeImmutable $aanmeldenTot
	 */
	public function setAanmeldenTot(DateTimeImmutable $aanmeldenTot): void;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getBewerkenTot(): ?DateTimeImmutable;

	/**
	 * @param DateTimeImmutable|null $bewerkenTot
	 */
	public function setBewerkenTot(?DateTimeImmutable $bewerkenTot): void;

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getAfmeldenTot(): ?DateTimeImmutable;

	/**
	 * @param DateTimeImmutable|null $afmeldenTot
	 */
	public function setAfmeldenTot(?DateTimeImmutable $afmeldenTot): void;
}
