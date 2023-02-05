<?php /** @noinspection PhpUnused wordt gebruikt in templates*/

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\entity\MenuItem;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Hulpmethodes die gebruikt worden in views.
 */

/**
 * Zie https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table voor de geaccepteerde formats
 *
 * @param DateTimeInterface $date
 * @param $format
 * @return false|string
 */
function date_format_intl(DateTimeInterface $date, $format)
{
	$fmt = new IntlDateFormatter('nl', null, null);
	$fmt->setPattern($format);

	return $fmt->format($date);
}
