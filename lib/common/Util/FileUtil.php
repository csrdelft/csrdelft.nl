<?php

namespace CsrDelft\common\Util;

final class FileUtil
{
	/**
	 * Controleer of een mime-type bij een bestandsnaam past, onbekende bestandsnamen worden afgewezen.
	 *
	 * @param string $filename
	 * @param string $mime
	 * @return bool
	 */
	public static function checkMimetype($filename, $mime)
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		$mimeToExtension = [
			'application/atom+xml' => 'atom',
			'application/cu-seeme' => 'cu',
			'application/epub+zip' => 'epub',
			'application/force-download' => 'mp3',
			'application/gzip' => 'gz',
			'application/java-archive' => 'jar',
			'application/json' => 'json',
			'application/msword' => 'doc',
			'application/octet-stream' => 'rar',
			'.kdbx',
			'application/ogg' => 'ogx',
			'application/pdf' => 'pdf',
			'application/pkix-cert' => 'cer',
			'application/pkix-crl' => 'crl',
			'application/postscript' => ['ai', 'eps', 'ps'],
			'application/rar' => 'rar',
			'application/rar-x' => 'rar',
			'application/rss+xml' => 'rss',
			'application/rtf' => 'rtf',
			'application/vnd.ms-excel' => 'xls',
			'application/vnd.ms-fontobject' => 'eot',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.openxmlformats-officedocument.pres' => 'pptx',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>
				'pptx',
			'application/vnd.openxmlformats-officedocument.spre' => 'xlsx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>
				'xlsx',
			'application/vnd.openxmlformats-officedocument.word' => 'docx',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' =>
				'docx',
			'application/wsdl+xml' => 'wsdl',
			'application/x-7z-compressed' => '7z',
			'application/x-bittorrent' => 'torrent',
			'application/x-bzip2' => 'bz2',
			'application/x-debian-package' => 'deb',
			'application/x-dvi' => 'dvi',
			'application/x-font-ttf' => 'ttf',
			'application/x-font-woff' => 'woff',
			'application/x-iso9660-image' => 'iso',
			'application/x-latex' => 'latex',
			'application/x-pdf' => 'pdf',
			'application/x-rar' => 'rar',
			'application/x-rar-compressed' => 'rar',
			'application/x-shockwave-flash' => 'swf',
			'application/x-tar' => 'tar',
			'application/x-x509-ca-cert' => 'crt',
			'application/x-zip-compressed' => 'zip',
			'application/xml' => 'xml',
			'application/zip' => 'zip',
			'audio/flac' => 'flac',
			'audio/midi' => ['mid', 'midi'],
			'audio/mp3' => 'mp3',
			'audio/mp4' => 'm4a',
			'audio/mpeg' => 'mp3',
			'audio/ogg' => ['oga', 'ogg', 'ogv'],
			'audio/x-aac' => 'aac',
			'audio/x-aiff' => 'aif',
			'audio/x-ms-wma' => 'wma',
			'audio/x-wav' => 'wav',
			'image/bmp' => 'bmp',
			'image/gif' => 'gif',
			'image/jpeg' => ['jpe', 'jpeg', 'jpg'],
			'image/png' => 'png',
			'image/svg+xml' => 'svg',
			'image/tiff' => ['tif', 'tiff'],
			'image/x-cmu-raster' => 'ras',
			'image/x-icon' => 'ico',
			'image/x-portable-anymap' => 'pnm',
			'image/x-portable-bitmap' => 'pbm',
			'image/x-portable-graymap' => 'pgm',
			'image/x-portable-pixmap' => 'ppm',
			'image/x-wmf' => 'wmf',
			'image/x-xbitmap' => 'xbm',
			'image/x-xpixmap' => 'xpm',
			'image/x-xwindowdump' => 'xwd',
			'text/calendar' => 'ics',
			'text/css' => 'css',
			'text/csv' => 'csv',
			'text/html' => ['htm', 'html'],
			'text/javascript' => 'js',
			'text/pdf' => 'pdf',
			'text/plain' => ['asc', 'ini', 'log', 'txt'],
			'text/rtf' => 'rtf',
			'text/sgml' => ['sgm', 'sgml'],
			'text/x-setext' => 'etx',
			'text/yaml' => ['yaml', 'yml'],
			'video/mp4' => ['m4v', 'mp4', 'mp4a', 'mp4v', 'mpg4'],
			'video/mpeg' => ['mpe', 'mpeg', 'mpg'],
			'video/quicktime' => ['mov', 'qt'],
			'video/webm' => 'webm',
			'video/x-flv' => 'flv',
			'video/x-ms-asf' => 'asf',
			'video/x-ms-wmv' => 'wmv',
			'video/x-msvideo' => 'avi',
		];

		$expectedExtension = $mimeToExtension[$mime] ?? null;

		if (is_null($expectedExtension)) {
			return false;
		} else {
			if (is_array($expectedExtension)) {
				return in_array($extension, $expectedExtension);
			} else {
				return $extension === $expectedExtension;
			}
		}
	}

	public static function delTree($dir): bool
	{
		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			is_dir("$dir/$file")
				? static::delTree("$dir/$file")
				: unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	public static function format_filesize($size): string
	{
		$units = [' B', ' KB', ' MB', ' GB', ' TB'];
		for ($i = 0; $size >= 1024 && $i < 4; $i++) {
			$size /= 1024;
		}
		return round($size, 1) . $units[$i];
	}

	public static function getMaximumFileUploadSize(): int|string|false
	{
		return min(
			FileUtil::convertPHPSizeToBytes(ini_get('post_max_size')),
			FileUtil::convertPHPSizeToBytes(ini_get('upload_max_filesize'))
		);
	}

	/**
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
	 *
	 * @source http://stackoverflow.com/a/22500394
	 * @param $sSize
	 * @return false|int|string
	 */
	public static function convertPHPSizeToBytes($sSize)
	{
		if (is_numeric($sSize)) {
			return $sSize;
		}
		$sSuffix = substr($sSize, -1);
		$iValue = substr($sSize, 0, -1);
		switch (strtoupper($sSuffix)) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'P':
				$iValue *= 1024;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'T':
				$iValue *= 1024;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'G':
				$iValue *= 1024;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'M':
				$iValue *= 1024;
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'K':
				$iValue *= 1024;
			default:
				break;
		}
		return $iValue;
	}

	/**
	 * Plaatje vierkant croppen.
	 * @source http://abeautifulsite.net/blog/2009/08/cropping-an-image-to-make-square-thumbnails-in-php/
	 * @param $src_image
	 * @param $dest_image
	 * @param int $thumb_size
	 * @param int $jpg_quality
	 * @return bool
	 */
	public static function square_crop(
		$src_image,
		$dest_image,
		$thumb_size = 64,
		$jpg_quality = 90
	) {
		// Get dimensions of existing image
		$image = getimagesize($src_image);

		// Check for valid dimensions
		if ($image[0] <= 0 || $image[1] <= 0) {
			return false;
		}

		// Determine format from MIME-Type
		$image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

		// Import image
		switch ($image['format']) {
			case 'jpg':
			case 'jpeg':
				$image_data = imagecreatefromjpeg($src_image);
				break;
			case 'png':
				$image_data = imagecreatefrompng($src_image);
				break;
			case 'gif':
				$image_data = imagecreatefromgif($src_image);
				break;
			default:
				// Unsupported format
				return false;
		}

		// Verify import
		if ($image_data == false) {
			return false;
		}

		// Calculate measurements
		if ($image[0] > $image[1]) {
			// For landscape images
			$x_offset = ($image[0] - $image[1]) / 2;
			$y_offset = 0;
			$square_size = $image[0] - $x_offset * 2;
		} else {
			// For portrait and square images
			$x_offset = 0;
			$y_offset = ($image[1] - $image[0]) / 2;
			$square_size = $image[1] - $y_offset * 2;
		}

		// Resize and crop
		$canvas = imagecreatetruecolor($thumb_size, $thumb_size);
		if (
			imagecopyresampled(
				$canvas,
				$image_data,
				0,
				0,
				$x_offset,
				$y_offset,
				$thumb_size,
				$thumb_size,
				$square_size,
				$square_size
			)
		) {
			// Create thumbnail
			switch (strtolower(preg_replace('/^.*\./', '', $dest_image))) {
				case 'jpg':
				case 'jpeg':
					$return = imagejpeg($canvas, $dest_image, $jpg_quality);
					break;
				case 'png':
					$return = imagepng($canvas, $dest_image);
					break;
				case 'gif':
					$return = imagegif($canvas, $dest_image);
					break;
				default:
					// Unsupported format
					$return = false;
					break;
			}

			//plaatje ook voor de webserver leesbaar maken.
			if ($return) {
				chmod($dest_image, 0644);
			}
			return $return;
		} else {
			return false;
		}
	}
}
