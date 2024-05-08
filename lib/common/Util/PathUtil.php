<?php

namespace CsrDelft\common\Util;

final class PathUtil
{
	public static function to_unix_path($path): string|array
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
	public static function safe_combine_path($folder, $subpath): ?string
	{
		if ($folder == null || $subpath == null) {
			return null;
		}
		$combined = $folder;
		if (!str_ends_with($combined, '/')) {
			$combined .= '/';
		}
		$combined .= $subpath;
		if (!str_starts_with(realpath($combined), realpath($folder))) {
			return null;
		}
		return $combined;
	}

	public static function realpathunix($path)
	{
		return PathUtil::to_unix_path(realpath($path));
	}

	public static function join_paths(...$args): string|array|null
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
	public static function path_valid($prefix, $path): bool
	{
		return str_starts_with(
			PathUtil::realpathunix(PathUtil::join_paths($prefix, $path)),
			PathUtil::realpathunix($prefix)
		);
	}

	/**
	 * rawurlencode() met uitzondering van slashes.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function direncode($url): string|array
	{
		return str_replace('%2F', '/', rawurlencode($url));
	}

	/**
	 * Remove unsafe characters from filename
	 * @param $name string
	 *
	 * @return bool
	 */
	public static function filter_filename($name): string|array|null
	{
		//Remove dots in front of filename to prevent directory traversal
		$name = ltrim($name, '.');

		return preg_replace('/[^a-z0-9 \-_()éê\.]/i', ' ', $name);
	}

	/**
	 * @param $name string
	 *
	 * @return bool
	 */
	public static function valid_filename($name): int|false
	{
		return preg_match('/^(?:[a-z0-9 \-_()éê]|\.(?!\.))+$/iD', $name);
	}
}
