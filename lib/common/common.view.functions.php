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
 * Zorgt dat line endings CRLF zijn voor ical en vcard.
 *
 * @param string input
 * @return string
 */
function crlf_endings(string $input) {
	return str_replace("\n", "\r\n", $input);
}

function bbcode(string $string, string $mode = 'normal') {
	if ($mode === 'html') {
		return CsrBB::parseHtml($string);
	} else if ($mode == 'mail') {
		return CsrBB::parseMail($string);
	} else {
		return CsrBB::parse($string);
	}
}

/**
 * @param int $bedrag Bedrag in centen
 * @return string Geformat met euro
 */
function format_bedrag($bedrag) {
	return '€' . format_bedrag_kaal($bedrag);
}

/**
 * @param int $bedrag Bedrag in euros
 * @return string Geformat met euro, bij hele euro's met ",-"
 */
function format_euro($bedrag) {
	$bedragtekst = sprintf('%.2f', $bedrag);
	$leesbaar = str_replace(',00', ',-', $bedragtekst);
	return '€ ' . $leesbaar;
}

/**
 * @param int $bedrag Bedrag in centen
 * @return string Geformat zonder euro
 */
function format_bedrag_kaal($bedrag) {
	return sprintf('%.2f', $bedrag / 100);
}

/**
 * @param string $string input string
 * @param integer $length length of truncated text
 * @param string $etc end string
 * @param boolean $break_words truncate at word boundary
 * @param boolean $middle truncate in the middle of text
 *
 * @return string truncated string
 */
function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
	if ($length === 0) {
		return '';
	}
	if (mb_strlen($string, 'UTF-8') > $length) {
		$length -= min($length, mb_strlen($etc, 'UTF-8'));
		if (!$break_words && !$middle) {
			$string = preg_replace(
				'/\s+?(\S+)?$/u',
				'',
				mb_substr($string, 0, $length + 1, 'UTF-8')
			);
		}
		if (!$middle) {
			return mb_substr($string, 0, $length, 'UTF-8') . $etc;
		}
		return mb_substr($string, 0, $length / 2, 'UTF-8') . $etc .
			mb_substr($string, -$length / 2, $length, 'UTF-8');
	}
	return $string;
}

/**
 * Finds the position of the first space before a given offset.
 *
 * @param string $string
 * @param int $offset
 * @return bool|int
 */
function first_space_before(string $string, int $offset = null) {
	return mb_strrpos(substr($string, 0, $offset), ' ') + 1;
}

/**
 * Finds the position of the first space after a given offset.
 *
 * @param string $string
 * @param int $offset
 * @return bool|int
 */
function first_space_after(string $string, int $offset = null) {
	return mb_strpos($string, ' ', $offset);
}

/**
 * Split a string on keyword with a given space (in characters) around the keyword. Splits on spaces.
 *
 * @param string $string The base string
 * @param string $keyword The keyword to split on
 * @param int $space_around Amount of characters after which a split should occur
 * @param int $threshold Least amount of characters that should be hidden for a split to occur
 * @param string $ellipsis Character(s) to use as ellipsis character. default: …
 * @return string
 */
function split_on_keyword(string $string, string $keyword, int $space_around = 100, int $threshold = 10, string $ellipsis = '…') {
	$prevPos = $lastPos = 0;
	$firstPos = mb_stripos($string, $keyword);

	if ($firstPos === false && mb_strlen($string)) {
		return mb_substr($string, 0, $space_around * 2) . $ellipsis;
	}

	if ($firstPos > $space_around) {
		$split = first_space_before($string, $firstPos - $space_around);

		if ($split > $threshold) {
			$string = $ellipsis . mb_substr($string, $split);
			$prevPos = mb_strlen($ellipsis) + $split + mb_strlen($keyword);
		}
	}

	while ($prevPos < mb_strlen($string) && ($lastPos = mb_stripos($string, $keyword, $prevPos)) !== false) {
		// Split and insert ellipsis if the space between keywords is large enough.
		if ($lastPos - $prevPos > 2 * $space_around) {
			$split_l = first_space_after($string, $prevPos + $space_around);
			$split_r = first_space_before($string, $lastPos - $space_around);

			// Only do the split if enough characters are hidden by splitting
			if ($split_r - $split_l > $threshold) {
				$string = mb_substr($string, 0, $split_l) . $ellipsis . mb_substr($string, $split_r);
				$prevPos = $split_l + 2 * ($split_r - $split_l) + mb_strlen($ellipsis) + mb_strlen($keyword);

				continue;
			}
		}

		$prevPos = $lastPos + mb_strlen($keyword);
	}

	if ($prevPos + $space_around < mb_strlen($string)) {
		$string = mb_substr($string, 0, first_space_after($string, $prevPos + $space_around)) . $ellipsis;
	}

	return $string;
}

/**
 * Ical escape modifier plugin
 * Type:     modifier<br>
 * Name:     escape_ical<br>
 * Purpose:  escape string for ical output
 *
 * @param string $string
 * @return string
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
function escape_ical($string) {
	$string = str_replace('\\', '\\\\', $string);
	$string = str_replace("\r", '', $string);
	$string = str_replace("\n", '\n', $string);
	$string = str_replace(';', '\;', $string);
	return str_replace(',', '\,', $string);
}

/**
 * Zie http://userguide.icu-project.org/formatparse/datetime voor de geaccepteerde formats
 *
 * @param DateTimeInterface $date
 * @param $format
 * @return false|string
 */
function date_format_intl(DateTimeInterface $date, $format) {
	$fmt = new IntlDateFormatter('nl', null, null);
	$fmt->setPattern($format);

	return $fmt->format($date);
}

function commitHash($full = false) {
	if ($full) {
		return trim(`git rev-parse HEAD`);
	} else {
		return trim(`git rev-parse --short HEAD`);
	}
}

function commitLink() {
	return 'https://github.com/csrdelft/productie/commit/' . commitHash(true);
}

function reldate($datum) {
	if ($datum instanceof DateTimeInterface) {
		$moment = $datum->getTimestamp();
	} else {
		$moment = strtotime($datum);
	}

	if (date('Y-m-d') == date('Y-m-d', $moment)) {
		$return = 'vandaag om ' . strftime('%H:%M', $moment);
	} elseif (date('Y-m-d', $moment) == date('Y-m-d', strtotime('1 day ago'))) {
		$return = 'gisteren om ' . strftime('%H:%M', $moment);
	} else {
		$return = strftime('%A %e %B %Y om %H:%M', $moment); // php-bug: %e does not work on Windows
	}
	return '<time class="timeago" title="'.$return.'" datetime="' . date('Y-m-d\TG:i:sO', $moment) . '">' . $return . '</time>'; // ISO8601
}
