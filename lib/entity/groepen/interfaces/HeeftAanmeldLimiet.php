<?php
namespace CsrDelft\entity\groepen\interfaces;

use CsrDelft\entity\security\enum\AccessAction;

/**
 * Interface HeeftAanmeldLimiet
 *
 * Om aan te geven dat iets (in dit geval een groep) een aanmeldlimiet heeft.
 *
 * @package CsrDelft\model\entity\interfaces
 */
interface HeeftAanmeldLimiet
{
	function getAanmeldLimiet();
	function magAanmeldLimiet(AccessAction $action);
}
