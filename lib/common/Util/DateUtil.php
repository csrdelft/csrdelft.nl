<?php

namespace CsrDelft\common\Util;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use IntlDateFormatter;

final class DateUtil
{
	public const LONG_DATE_FORMAT = 'EE d MMM'; // Ma 3 Jan
	public const DATE_FORMAT = 'y-MM-dd';
	public const DATETIME_FORMAT = 'y-MM-dd HH:mm:ss';
	public const TIME_FORMAT = 'HH:mm';
	public const FULL_TIME_FORMAT = 'HH:mm:ss';

	/**
	 * @param DateTimeInterface|string $datum
	 *
	 * @return string|false
	 */
	public static function reldate($datum)
	{
		if (!$datum instanceof DateTimeImmutable) {
			if ($datum instanceof DateTimeInterface) {
				$datum = DateTimeImmutable::createFromInterface($datum);
			} else {
				$datum = new DateTimeImmutable($datum);
			}
		}
		$vandaag = (new DateTimeImmutable())->setTime(0, 0);
		$gisteren = $vandaag->sub(new DateInterval('P1D')); // P1D == 1 dag

		if ($datum->format('Y-m-d') === $vandaag->format('Y-m-d')) {
			$return = 'vandaag om ' . self::dateFormatIntl($datum, "hh':'mm");
		} elseif ($datum->format('Y-m-d') === $gisteren->format('Y-m-d')) {
			$return = 'gisteren om ' . self::dateFormatIntl($datum, "hh':'mm");
		} else {
			// zelfde jaar: geen jaar laten zien
			$format =
				$datum->format('Y') === $vandaag->format('Y')
					? "eeee d MMMM 'om' hh':'mm"
					: "eeee d MMMM yyyy 'om' hh':'mm";
			$return = self::dateFormatIntl($datum, $format);
		}
		if ($return === '') {
			error_log('wtf');
		}
		return '<time class="timeago" title="' .
			$return .
			'" datetime="' .
			$datum->format(DateTimeImmutable::ATOM) .
			'">' .
			$return .
			'</time>';
	}

	/**
	 * @param string $date
	 * @param string $format
	 * @return true als huidige datum & tijd voorbij gegeven datum en tijd zijn
	 */
	public static function isDatumVoorbij(string $date, $format = 'Y-m-d H:i:s')
	{
		$date = date_create_immutable_from_format($format, $date);
		$now = date_create_immutable();
		return $now >= $date;
	}

	/**
	 * @param int $timestamp optional
	 *
	 * @return string current DateTime formatted Y-m-d H:i:s
	 */
	public static function getDateTime($timestamp = null)
	{
		if ($timestamp === null) {
			$timestamp = time();
		}
		return date('Y-m-d H:i:s', $timestamp);
	}

	/**
	 * Zie https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table voor de geaccepteerde formats
	 *
	 * @param DateTimeInterface $date
	 * @param $format
	 * @return false|string
	 */
	public static function dateFormatIntl(DateTimeInterface $date, $format)
	{
		$fmt = new IntlDateFormatter(
			'nl',
			IntlDateFormatter::NONE,
			IntlDateFormatter::NONE
		);
		$fmt->setPattern($format);
		return $fmt->format($date);
	}
}
