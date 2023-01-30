<?php

use CsrDelft\DataFixtures\AccountFixtures;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
	protected function setUp(): void
	{
		// Voorpagina crasht als er geen fotoalbum dir is.
		if (!file_exists(PHOTOALBUM_PATH)) {
			mkdir(PHOTOALBUM_PATH, 0777, true);
		}

		$this->client = static::createPantherClient();
	}

	protected function tearDown(): void
	{
		$this->client->request('GET', '/logout');
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

		$civiSaldoCell = $crawler->filter('.cell-civi-saldo')->text();

		$this->assertStringContainsString('Civisaldo', $civiSaldoCell);
		$this->assertStringContainsString('€ 0,00', $civiSaldoCell);
		$this->assertStringContainsString('Inleggen?', $civiSaldoCell);
	}

	public function testToestemming()
	{
		$crawler = $this->login();

		$crawler->filter('#algemeen_vereniging_ja')->click();
		$crawler->filter('#algemeen_bijzonder_ja')->click();
		$crawler->filter('#algemeen_foto_extern_ja')->click();
		$crawler->filter('#algemeen_foto_intern_ja')->click();
		$crawler->filter('#toestemming-ja')->click();

		$crawler->filter('.SubmitKnop')->click();

		$this->client
			->wait()
			->until(
				WebDriverExpectedCondition::invisibilityOfElementLocated(
					WebDriverBy::cssSelector('.modal')
				)
			);

		$this->assertTrue(true, 'Ben hier gekomen.');
	}

	/**
	 * @return \Symfony\Component\DomCrawler\Crawler|\Symfony\Component\Panther\DomCrawler\Crawler|null
	 */
	private function login()
	{
		$crawler = $this->client->request('GET', '/');

		$crawler->selectLink('Inloggen')->click();

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = AccountFixtures::UID_PUBCIE;
		$form['_password'] = 'stek open u voor mij!';

		$crawler = $this->client->submit($form);
		return $crawler;
	}
}
