<?php

namespace CsrDelft\tests;

use CsrDelft\DataFixtures\AccountFixtures;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
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
	 * @var \Symfony\Component\Panther\Client
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
			echo 'Toestemming is al gegeven!' . PHP_EOL;
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

		$crawler->selectLink('Inloggen')->click();

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = AccountFixtures::UID_PUBCIE;
		$form['_password'] = 'stek open u voor mij!';

		$crawler = $this->client->submit($form);

		$this->assertNotNull($crawler, 'Inlogform opslaan mislukt');

		return $this->geefToestemmingAlsNodig($crawler);
	}

	protected function setUp(): void
	{
		// Voorpagina crasht als er geen fotoalbum dir is.
		if (!file_exists(PHOTOALBUM_PATH)) {
			mkdir(PHOTOALBUM_PATH, 0777, true);
		}

		$this->client = static::createPantherClient();
	}

	/**
	 * Logout na iedere test om schoon te beginnen.
	 */
	protected function tearDown(): void
	{
		$status = $this->getStatus();
		if (
			$status == BaseTestRunner::STATUS_ERROR ||
			$status == BaseTestRunner::STATUS_FAILURE
		) {
			$this->client->takeScreenshot(
				__DIR__ . '/../../screenshot/failure-' . $this->getName() . '.png'
			);

			try {
				$this->client
					->findElement(WebDriverBy::cssSelector('.invalid-feedback'))
					->getLocationOnScreenOnceScrolledIntoView();

				$this->client->takeScreenshot(
					__DIR__ . '/../../screenshot/failure-' . $this->getName() . '-2.png'
				);
			} catch (NoSuchElementException $e) {
				// Negeer
			}
		}
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
		$webDriverElement = $this->client->findElement(
			WebDriverBy::linkText($linkText)
		);

		$webDriverElement->getLocationOnScreenOnceScrolledIntoView();

		$this->client
			->wait()
			->until(WebDriverExpectedCondition::visibilityOf($webDriverElement));

		try {
			return $this->client->clickLink($linkText);
		} catch (ElementClickInterceptedException $e) {
			sleep(1); // Zijn nog aan het scrollen, probeer na een tijdje nog een keer.
			// Dit zou geen probleem _moeten_ zijn.
			return $this->client->clickLink($linkText);
		}
	}
}
