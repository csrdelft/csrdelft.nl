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


	/**
	 * Timestamp van beginmoment.
	 */
	public function getBeginMoment(): DateTimeImmutable;

	/**
	 * Timestamp van eindmoment.
	 */
	public function getEindMoment(): DateTimeImmutable;

	public function getTitel(): string;

	public function getUrl(): ?string;
}
