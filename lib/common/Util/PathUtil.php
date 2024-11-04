<?php

namespace CsrDelft\common\Util;

final class PathUtil
{
	/**
	 * @param false|string $path
	 *
	 * @return string|string[]
	 *
	 * @psalm-return array<string>|string
	 */
	public static function to_unix_path(string|false $path): array|string
	{
		return str_replace(DIRECTORY_SEPARATOR, '/', $path);
	}

	/**
	 * Combines two parts of a file path safely, meaning that the resulting path will be inside $folder.
	 * If directory traversal is applied using ../ et cetera, making the path no longer be inside $folder, null is returned;
	 * @param $folder
	 * @param $subpath
	 * @return string|null
	 */
	public static function safe_combine_path(string $folder, string $subpath)
	{
		if ($folder == null || $subpath == null) {
			return null;
		}
		$combined = $folder;
		if (!str_ends_with((string) $combined, '/')) {
			$combined .= '/';
		}
		$combined .= $subpath;
		if (!str_starts_with(realpath($combined), realpath($folder))) {
			return null;
		}
		return $combined;
	}

	public static function realpathunix(string $path)
	{
		return PathUtil::to_unix_path(realpath($path));
	}

	public static function join_paths(string ...$args): string|null
	{
		$paths = [];

		foreach ($args as $arg) {
			if ($arg !== '') {
				$paths[] = $arg;
			}
		}

		return preg_replace('#/+#', '/', join('/', $paths));
	}

	/**
	 * Checks if $path exists in $prefix and if it is still inside $prefix.
	 *
	 * @param $prefix
	 * @param $path
	 * @return bool
	 */
	public static function path_valid(string $prefix, $path)
	{
		return str_starts_with(
			(string) PathUtil::realpathunix(PathUtil::join_paths($prefix, $path)),
			(string) PathUtil::realpathunix($prefix)
		);
	}

	/**
	 * rawurlencode() met uitzondering van slashes.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function direncode($url)
	{
		return str_replace('%2F', '/', rawurlencode($url));
	}

	/**
	 * @param $name string
	 *
	 * @return false|int
	 *
	 * @psalm-return 0|1|false
	 */
	public static function valid_filename(string $name): int|false
	{
		return preg_match('/^(?:[a-z0-9 \-_()éê]|\.(?!\.))+$/iD', (string) $name);
	}
}
