<?php
error_reporting(0);
$isSameHost = strcasecmp($_SERVER['HTTP_HOST'], parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) === 0;
$isColorSet = array_key_exists('color', $_GET);
if ($isSameHost && $isColorSet) {

    list($red, $green, $blue) = str_split($_GET['color'], 2);

    $img = imagecreate(16, 16);
    imagefill($img, 0, 0, imagecolorallocate($img, hexdec($red), hexdec($green), hexdec($blue)));
    header('Content-type: image/png');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60*60*24*365) . ' GMT');
    imagepng($img);
}
