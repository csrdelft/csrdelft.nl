<?php

namespace CsrDelft\entity\agenda;

use DateTimeImmutable;

/**
 * Agendeerbaar.interface.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Item dat in de agenda kan worden weergegeven.
 *
 */
interface Agendeerbaar
{
	public function getUUID(): string;

	/**
	 * Timestamp van beginmoment.
	 */
	public function getBeginMoment(): DateTimeImmutable;

	/**
	 * Timestamp van eindmoment.
	 */
	public function getEindMoment(): DateTimeImmutable;

	public function getTitel(): string;

	public function getBeschrijving(): ?string;

	public function getLocatie(): ?string;

	public function getUrl(): ?string;

	public function isHeledag(): bool;

	public function isTransparant(): bool;
}
