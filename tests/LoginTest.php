<?php


use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
	protected function tearDown(): void
	{
		static::createPantherClient()->get('/logout');
	}

	private function login(Client $client): Crawler
	{
		$crawler = $client->request('GET', '/');

		$crawler->selectLink("Inloggen")->click();

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = 'x101';
		$form['_password'] = 'stek open u voor mij!';

		return $client->submit($form);
	}

	public function testPageLoad()
	{
		$client = static::createPantherClient();

		$client->request('GET', '/');

		// Check of we hier zijn aangekomen
		$this->assertTrue(true);
	}

	public function testLogin()
	{
		$client = static::createPantherClient();

		$crawler = $this->login($client);

		$pageContent = $crawler->filter('.cd-page-content')->text();

		$this->assertStringContainsString('Dit is de voorpagina.', $pageContent);
	}

	public function testToestemming()
	{
		$client = static::createPantherClient();

		$this->login($client);

		$crawler = $client->request('GET', '/');

		$form = $crawler->filter('#modal form')->form();

		$form['algemeen_vereniging'] = 'ja';
		$form['algemeen_bijzonder'] = 'ja';
		$form['algemeen_foto_extern'] = 'ja';
		$form['algemeen_foto_intern'] = 'ja';

		$client->executeScript("document.getElementById('toestemming-ja').scrollIntoView();");

		$crawler->filter('#toestemming-ja')->click();

		$crawler->selectLink('Opslaan')->click();

		$crawler = $client->getCrawler();

		$this->assertEquals("Toestemming opgeslagen", $crawler->filter('#melding')->text());
	}

}
