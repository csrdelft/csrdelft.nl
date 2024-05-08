<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\common\Util\DateUtil;
use DateTime;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeTwigExtension extends AbstractExtension
{
	public function getFilters()
	{
		return [
			new TwigFilter(
				'reldate',
				[DateUtil::class, 'reldate'],
				['is_safe' => ['html']]
			),
			new TwigFilter('date_format', [$this, 'twig_date_format']),
			new TwigFilter('datetime_format', [$this, 'twig_datetime_format']),
			new TwigFilter('datetime_format_long', [
				$this,
				'twig_datetime_format_long',
			]),
			new TwigFilter('time_format', [$this, 'twig_time_format']),
			new TwigFilter(
				'rfc2822',
				[$this, 'twig_rfc2822'],
				['is_safe' => ['html']]
			),
			new TwigFilter(
				'zijbalk_date_format',
				[$this, 'twig_zijbalk_date_format'],
				['is_safe' => ['html']]
			),
			new TwigFilter('date_format_intl', [DateUtil::class, 'dateFormatIntl']),
			new TwigFilter('date_create', [$this, 'twig_date_create']),
		];
	}

	public function twig_date_format($date)
	{
		return DateUtil::dateFormatIntl($date, DateUtil::DATE_FORMAT);
	}

	public function twig_time_format($date)
	{
		return DateUtil::dateFormatIntl($date, DateUtil::TIME_FORMAT);
	}

	public function twig_datetime_format($datetime)
	{
		return DateUtil::dateFormatIntl($datetime, DateUtil::DATETIME_FORMAT);
	}

	public function twig_datetime_format_long($datetime)
	{
		return DateUtil::dateFormatIntl($datetime, DateUtil::LONG_DATE_FORMAT);
	}

	/**
	 * Formatteer een datum voor de zijbalk.
	 *
	 *  - Als dezelfe dag:     13:13
	 *  - Als dezelfde maand:  ma 06
	 *  - Anders:              06-12
	 *
	 * @version 1.0
	 * @param string|integer
	 * @return string
	 */
	public function twig_zijbalk_date_format(DateTimeInterface $datetime): string|false
	{
		$datetime = $datetime->getTimestamp();

		if (date('d-m', $datetime) === date('d-m')) {
			return date('H:i', $datetime);
		} elseif (date('N', $datetime) === date('N')) {
			return date('D d', $datetime);
		} else {
			return date('d-m', $datetime);
		}
	}

	/**
	 * @param $date
	 * @return false|string
	 */
	public function twig_rfc2822(DateTimeInterface $date): string
	{
		$date = $date->getTimestamp();
		if (strlen($date) == strlen((int) $date)) {
			return date('r', $date);
		} else {
			return date('r', strtotime($date));
		}
	}

	public function twig_date_create($date, $format): DateTime|false
	{
		return DateTime::createFromFormat($format, $date);
	}
}
