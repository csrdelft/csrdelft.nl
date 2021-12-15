<?php

/**
 * Checkt of de stek kaput is.
 */

use Doctrine\DBAL\DriverManager;
use Maknz\Slack\Client as SlackClient;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__FILE__) . '/../vendor/autoload.php';


// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_array($env = @include dirname(__DIR__) . '/.env.local.php')) {
	foreach ($env as $k => $v) {
		$_ENV[$k] = $_ENV[$k] ?? (isset($_SERVER[$k]) && 0 !== strpos($k, 'HTTP_') ? $_SERVER[$k] : $v);
	}
} elseif (!class_exists(Dotenv::class)) {
	throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} else {
	// load all the .env files
	(new Dotenv())->loadEnv(dirname(__DIR__) . '/.env');
}

$pdo = DriverManager::getConnection([
	'url' => $_ENV['DATABASE_URL'],
	'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
])->getWrappedConnection();

$query = $pdo->prepare("SELECT * FROM information_schema.PROCESSLIST WHERE COMMAND = 'Sleep';");
$query->execute();

if ($query->rowCount() > 10) {
	mail($_ENV['EMAIL_PUBCIE'], "Stek kaput", var_export($query->fetchAll(), true));

	$slackClient = new SlackClient($_ENV['SLACK_URL'], [
		'username' => "Monitor",
		'channel' => "#general",
		'icon' => ":panik:",
	]);

	$slackClient->createMessage()->send(":panik: De stek ligt eruit!");
}
