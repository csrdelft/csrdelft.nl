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
	public function getUUID();

	/**
	 * Timestamp van beginmoment.
	 */
	public function getBeginMoment(): DateTimeImmutable;

	/**
	 * Timestamp van eindmoment.
	 */
	public function getEindMoment(): DateTimeImmutable;

	public function getTitel();

	public function getBeschrijving();

	public function getLocatie();

	public function getUrl();

	public function isHeledag();

	public function isTransparant();
}
