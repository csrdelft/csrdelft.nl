<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\controller\maalcie\CorveeRepetitiesController;
use CsrDelft\repository\corvee\CorveeTakenRepository;

/**
 * Data Transfer Object voor het updaten van repetitie taken
 *
 * @see CorveeTakenRepository
 * @see CorveeRepetitiesController
 *
 * @package CsrDelft\entity\corvee
 */
class RepetitieTakenUpdateDTO
{
	/** @var int */
	public $update;
	/** @var int */
	public $day;
	/** @var int */
	public $datum;
	/** @var int */
	public $maaltijd;
}
