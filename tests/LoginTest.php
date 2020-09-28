<?php


use CsrDelft\common\ContainerFacade;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
	public static function setUpBeforeClass(): void
	{
		self::bootKernel();
		ContainerFacade::init(self::$container);
		$application = new Application(self::$kernel);
		$application->setAutoExit(false);

		$doctrineDatabaseDrop = new ArrayInput(['command' => 'doctrine:database:drop', '--force' => true, '--no-interaction' => true,]);
		$doctrineDatabaseCreate = new ArrayInput(['command' => 'doctrine:database:create', '--no-interaction' => true,]);
		$doctrineMigrationsMigrate = new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true,]);
		$doctrineFixturesLoad = new ArrayInput(['command' => 'doctrine:fixtures:load', '--no-interaction' => true,]);

		$output = new BufferedOutput();

		$application->run($doctrineDatabaseDrop, $output);
		echo $output->fetch();

		$application->run($doctrineDatabaseCreate, $output);
		echo $output->fetch();

		$application->run($doctrineMigrationsMigrate, $output);
		echo $output->fetch();

		$application->run($doctrineFixturesLoad, $output);
		echo $output->fetch();
	}

	/**
	 * @var Client
	 */
	private $client;

	protected function setUp(): void
	{
		$this->client = static::createPantherClient();
	}

	protected function tearDown(): void
	{
		$this->client->get('/logout');
	}

	private function login(): Crawler
	{
		$crawler = $this->client->request('GET', '/');

		$crawler->selectLink("Inloggen")->click();

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = 'x101';
		$form['_password'] = 'stek open u voor mij!';

		return $this->client->submit($form);
	}

	public function testPageLoad()
	{
		$this->client->request('GET', '/');

		// Check of we hier zijn aangekomen
		$this->assertTrue(true);
	}

	public function testLogin()
	{
		$crawler = $this->login();

		$pageContent = $crawler->filter('.cd-page-content')->text();

		$this->assertStringContainsString('Dit is de voorpagina.', $pageContent);
	}

	public function testToestemming()
	{
		$this->login();

		$crawler = $this->client->request('GET', '/');

		$form = $crawler->filter('#modal form')->form();

		$form['algemeen_vereniging'] = 'ja';
		$form['algemeen_bijzonder'] = 'ja';
		$form['algemeen_foto_extern'] = 'ja';
		$form['algemeen_foto_intern'] = 'ja';

		$this->client->executeScript("document.getElementById('toestemming-ja').scrollIntoView();");

		$crawler->filter('#toestemming-ja')->click();

		$crawler->selectLink('Opslaan')->click();

		$crawler = $this->client->getCrawler();

		$this->assertEquals("Toestemming opgeslagen", $crawler->filter('#melding')->text());
	}

}
