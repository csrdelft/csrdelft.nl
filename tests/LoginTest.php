<?php


use CsrDelft\common\ContainerFacade;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase {
	public function testPageLoad() {
		$client = static::createClient();
		ContainerFacade::init(self::$container);

		$client->request('GET', '/');

		$this->assertResponseIsSuccessful();
	}

	public function testLogin() {
		$client = static::createClient();
		ContainerFacade::init(self::$container);

		$crawler = $client->request('GET', '/');

		$form = $crawler->selectButton('Inloggen')->form();

		$form['_username'] = 'x101';
		$form['_password'] = 'stek open u voor mij!';

		$client->submit($form);

		$crawler = $client->request('GET', '/');

		$this->assertResponseIsSuccessful();

		$pageContent = $crawler->filter('.cd-page-content')->text();

		$this->assertStringContainsString('Dit is de voorpagina.', $pageContent);
	}

}
