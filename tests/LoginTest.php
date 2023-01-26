<?php

use CsrDelft\common\ContainerFacade;
use CsrDelft\DataFixtures\AccountFixtures;
use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
	public function testPageLoad()
	{
		$client = static::createPantherClient();
		ContainerFacade::init(self::$container);

		$client->request('GET', '/');

		// Check of we hier zijn aangekomen
		$this->assertTrue(true);
	}

	public function testLogin()
	{
		$client = static::createPantherClient();

		$crawler = $client->request('GET', '/');

		$crawler->selectLink('Inloggen')->click();

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = AccountFixtures::UID_PUBCIE;
		$form['_password'] = 'stek open u voor mij!';

		$crawler = $client->submit($form);

		$civiSaldoCell = $crawler->filter('.cell-civi-saldo')->text();

		$this->assertStringContainsString('Civisaldo', $civiSaldoCell);
		$this->assertStringContainsString('€ 0,00', $civiSaldoCell);
		$this->assertStringContainsString('Inleggen?', $civiSaldoCell);
	}
}
