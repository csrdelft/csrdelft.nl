#!/usr/bin/php5
<?php
/**
 * cron.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor uitvoeren van CRON-jobs.
 * 
 * 'geinstalleerd' met:
 * svn:executable property
 * export EDITOR=nano
 * crontab -e
 * 0 1 * * * /usr/www/csrdelft.nl/bin/cron.php >> /srv/www/csrdelft.nl/data/log/cron.log 2>&1
 * test door ./cron.php te typen
 * 
 * @see http://www.cronjob.nl/
 */
session_id('cron-cli');

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

// Corvee herinneringen
try {
	require_once 'maalcie/model/CorveeHerinneringenModel.class.php';
	CorveeHerinneringenModel::stuurHerinneringen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'CorveeHerinneringenModel::stuurHerinneringen()', array(), $e);
}

// Forum opschonen
try {
	require_once 'MVC/model/ForumModel.class.php';
	ForumModel::instance()->opschonen();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'ForumModel::instance()->opschonen()', array(), $e);
}

// FotoAlbum opschonen
try {
	require_once 'MVC/model/FotoAlbumModel.class.php';
	FotoAlbumModel::instance()->cleanup();
	FotoModel::instance()->cleanup();
} catch (Exception $e) {
	DebugLogModel::instance()->log('cron.php', 'FotoAlbumModel::instance()->cleanup()', array(), $e);
}