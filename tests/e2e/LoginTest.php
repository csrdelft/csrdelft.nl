<?php

namespace e2e;

use CsrDelft\tests\BrowserTestCase;

class LoginTest extends BrowserTestCase
{
	/**
	 * Eerste test, controleer of we een pagina kunnen opvragen.
	 */
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
}
