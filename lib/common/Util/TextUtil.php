<?php

namespace CsrDelft\common\Util;

final class TextUtil
{
	/**
	 * @param string $string input string
	 * @param integer $length length of truncated text
	 * @param string $etc end string
	 * @param boolean $break_words truncate at word boundary
	 * @param boolean $middle truncate in the middle of text
	 *
	 * @return string truncated string
	 */
	public static function truncate(
		$string,
		$length = 80,
		$etc = '...',
		$break_words = false,
		$middle = false
	) {
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
			return mb_substr($string, 0, $length / 2, 'UTF-8') .
				$etc .
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
	private static function first_space_before(string $string, int $offset = null): int
	{
		return mb_strrpos(substr($string, 0, $offset), ' ') + 1;
	}

	/**
	 * Finds the position of the first space after a given offset.
	 *
	 * @param string $string
	 * @param int $offset
	 * @return bool|int
	 */
	private static function first_space_after(string $string, int $offset = null): int|false
	{
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
	public static function split_on_keyword(
		string $string,
		string $keyword,
		int $space_around = 100,
		int $threshold = 10,
		string $ellipsis = '…'
	): string {
		$prevPos = $lastPos = 0;
		$firstPos = mb_stripos($string, $keyword);

		if ($firstPos === false && mb_strlen($string)) {
			return mb_substr($string, 0, $space_around * 2) . $ellipsis;
		}

		if ($firstPos > $space_around) {
			$split = static::first_space_before($string, $firstPos - $space_around);

			if ($split > $threshold) {
				$string = $ellipsis . mb_substr($string, $split);
				$prevPos = mb_strlen($ellipsis) + $split + mb_strlen($keyword);
			}
		}

		while (
			$prevPos < mb_strlen($string) &&
			($lastPos = mb_stripos($string, $keyword, $prevPos)) !== false
		) {
			// Split and insert ellipsis if the space between keywords is large enough.
			if ($lastPos - $prevPos > 2 * $space_around) {
				$split_l = static::first_space_after($string, $prevPos + $space_around);
				$split_r = static::first_space_before(
					$string,
					$lastPos - $space_around
				);

				// Only do the split if enough characters are hidden by splitting
				if ($split_r - $split_l > $threshold) {
					$string =
						mb_substr($string, 0, $split_l) .
						$ellipsis .
						mb_substr($string, $split_r);
					$prevPos =
						$split_l +
						2 * ($split_r - $split_l) +
						mb_strlen($ellipsis) +
						mb_strlen($keyword);

					continue;
				}
			}

			$prevPos = $lastPos + mb_strlen($keyword);
		}

		if ($prevPos + $space_around < mb_strlen($string)) {
			$string =
				mb_substr(
					$string,
					0,
					static::first_space_after($string, $prevPos + $space_around)
				) . $ellipsis;
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
	public static function escape_ical($string): string|array
	{
		$string = str_replace('\\', '\\\\', $string);
		$string = str_replace("\r", '', $string);
		$string = str_replace("\n", '\n', $string);
		$string = str_replace(';', '\;', $string);
		return str_replace(',', '\,', $string);
	}

	/**
	 * Zorgt dat line endings CRLF zijn voor ical en vcard.
	 *
	 * @param string input
	 * @return string
	 */
	public static function crlf_endings(string $input): string|array
	{
		return str_replace("\n", "\r\n", $input);
	}

	/**
	 * @param string $voornaam
	 * @param string $tussenvoegsel
	 * @param string $achternaam
	 *
	 * @return string
	 */
	public static function aaidrom($voornaam, $tussenvoegsel, $achternaam): string
	{
		$voornaam = mb_strtolower($voornaam);
		$achternaam = mb_strtolower($achternaam);

		$voor = [];
		preg_match('/^([^aeiuoyáéíóúàèëïöü]*)(.*)$/u', $voornaam, $voor);
		$achter = [];
		preg_match('/^([^aeiuoyáéíóúàèëïöü]*)(.*)$/u', $achternaam, $achter);

		$nwvoor = preg_replace('/^Ij/', 'IJ', ucwords($achter[1] . $voor[2]), 1);
		$nwachter = preg_replace('/^Ij/', 'IJ', ucwords($voor[1] . $achter[2]), 1);

		return sprintf(
			'%s %s%s',
			$nwvoor,
			!empty($tussenvoegsel) ? $tussenvoegsel . ' ' : '',
			$nwachter
		);
	}

	/**
	 * @param $string string
	 *
	 * @return bool
	 */
	public static function is_utf8($string): bool
	{
		return TextUtil::checkEncoding($string, 'UTF-8');
	}

	public static function checkEncoding($string, $string_encoding): bool
	{
		$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
		$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
		return $string ===
			mb_convert_encoding(mb_convert_encoding($string, $fs, $ts), $ts, $fs);
	}

	public static function vue_encode($object): string
	{
		return htmlspecialchars(json_encode($object));
	}
}
