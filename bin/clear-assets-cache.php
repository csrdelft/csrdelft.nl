<?php
/**
 * clear-assets-cache
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */

use Stash\Driver\FileSystem;
use Stash\Pool;

require_once __DIR__ . '/../lib/defines.include.php';
require_once __DIR__ . '/../vendor/autoload.php';

$driver = new FileSystem(['path' => DATA_PATH . 'assets/']);
$cachePool = new Pool($driver);

$cachePool->clear();

$driver = new FileSystem(['path' => DATA_PATH . 'less/']);
$cachePool = new Pool($driver);

$cachePool->clear();

echo 'Cache cleared!' . PHP_EOL;
