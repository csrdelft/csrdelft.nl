<?php

namespace e2e;

use CsrDelft\tests\BrowserTestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys as Keys;
use Symfony\Component\DomCrawler\Crawler;

class ProfielTest extends BrowserTestCase
{
	public function testProfielPagina()
	{
		$this->login();

		$crawler = $this->client->request('GET', '/profiel');

		$bijnaam = $this->getProfielValue($crawler, 'Bijnaam');

		$this->assertEquals('pubcie', $bijnaam);
	}

	public function testProfielBewerken()
	{
		$this->login();

		$this->client->request('GET', '/profiel');

		$crawler = $this->clickLink('Profiel bewerken');
		$this->updateField($crawler, 'studie', 'TestStudie');
		$crawler = $this->clickLink('Opslaan');

		$this->assertStringEndsWith(
			'/profiel/x101',
			$this->client->getCurrentURL(),
			'Niet terug gekomen op de profiel pagina' . $crawler->text()
		);

		$this->assertEquals(
			'TestStudie',
			$this->getProfielValue($crawler, 'Studie')
		);

		$crawler = $this->clickLink('Profiel bewerken');
		$this->updateField($crawler, 'studie', 'TestStudie2');
		$crawler = $this->clickLink('Opslaan');

		$this->assertEquals(
			'TestStudie2',
			$this->getProfielValue($crawler, 'Studie')
		);
	}

	/**
	 * @param Crawler $crawler
	 * @param string $name
	 * @param string $newValue
	 * @return void
	 */
	private function updateField(
		Crawler $crawler,
		string $name,
		string $newValue
	): void {
		$input = $crawler->findElement(
			WebDriverBy::cssSelector('[name=' . $name . ']')
		);
		$value = $input->getDomProperty('value');
		// ->clear werkt niet
		$input->sendKeys(str_repeat(Keys::BACKSPACE, strlen($value)));
		$input->sendKeys($newValue);
	}

	/**
	 * Haal een specifieke waarde op van de profiel pagina.
	 *
	 * @param Crawler $crawler
	 * @param string $str
	 * @return string
	 */
	private function getProfielValue(Crawler $crawler, string $str): string
	{
		return $crawler
			->filter('#profiel')
			->filterXPath("//*[contains(text(), '$str')]")
			->nextAll()
			->first()
			->text();
	}
}
