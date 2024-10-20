<?php

namespace CsrDelft\tests;

use Symfony\Component\Panther\Client;
use CsrDelft\DataFixtures\AccountFixtures;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Runner\BaseTestRunner;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class BrowserTestCase extends PantherTestCase
{
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * @param Crawler $crawler
	 * @return Crawler Een crawler voor GET /
	 * @throws NoSuchElementException
	 * @throws TimeoutException
	 */
	public function geefToestemmingAlsNodig(Crawler $crawler): Crawler
	{
		if ($crawler->filter('.modal')->count() == 0) {
			return $crawler;
		}

		$modal = $crawler->filter('.modal')->first();

		$modal->filter('#algemeen_vereniging_ja')->click();
		$modal->filter('#algemeen_bijzonder_ja')->click();
		$modal->filter('#algemeen_foto_extern_ja')->click();
		$modal->filter('#algemeen_foto_intern_ja')->click();
		$modal->filter('#toestemming-ja')->click();

		$modal->filter('.SubmitKnop')->click();

		$this->client
			->wait()
			->until(
				WebDriverExpectedCondition::invisibilityOfElementLocated(
					WebDriverBy::cssSelector('.modal')
				)
			);

		$crawler = $this->client->request('GET', '/');

		$this->assertSelectorNotExists('.modal');

		return $crawler;
	}

	/**
	 * @return Crawler
	 * @throws NoSuchElementException
	 * @throws TimeoutException
	 */
	public function login(): Crawler
	{
		$crawler = $this->client->request('GET', '/');

		$this->clickLink('Inloggen');

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = AccountFixtures::UID_PUBCIE;
		$form['_password'] = 'stek open u voor mij!';

		$crawler = $this->client->submit($form);

		$this->assertNotNull($crawler, 'Inlogform opslaan mislukt');

		return $this->geefToestemmingAlsNodig($crawler);
	}

	protected function setUp(): void
	{
		$this->client = static::createPantherClient();
	}

	/**
	 * Logout na iedere test om schoon te beginnen.
	 */
	protected function tearDown(): void
	{
		$this->client->request('GET', '/logout');
	}

	/**
	 * @param $linkText
	 * @return Crawler
	 * @throws NoSuchElementException
	 * @throws TimeoutException
	 */
	protected function clickLink($linkText): Crawler
	{
		$webDriverElement = $this->client->getCrawler()->selectLink($linkText);
		$webDriverElement->getLocationOnScreenOnceScrolledIntoView();

		return $this->client->click($webDriverElement->link());
	}
}
