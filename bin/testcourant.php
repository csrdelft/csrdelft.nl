<?php

use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\entity\courant\CourantCategorie;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

include __DIR__ . "/../config/bootstrap.php";

class TestBbExtension extends AbstractExtension {
	public function getFilters() {
		return [
			new TwigFilter('bbcode', function ($val) {
				return $val;
			}, ['is-safe' => ['html']])
		];
	}
}

$twig = new Environment(new FilesystemLoader([__DIR__ . '/../templates/'], __DIR__ . '/../'));

$twig->addExtension(new TestBbExtension());

/**
 * @return CourantBericht
 */
function maakBericht($titel, $inhoud, $cat): CourantBericht {
	$bericht = new CourantBericht();
	$bericht->bericht = $inhoud;
	$bericht->titel = $titel;
	$bericht->cat = $cat;
	return $bericht;
}

$courant = $twig->render('courant/mail.html.twig', [
	'berichten' => [
		maakBericht('Voorwoord bericht', 'dit is het voorwoord van deze courant', CourantCategorie::VOORWOORD()),
		maakBericht('Titel van bericht', 'Dit is mijn bericht bla bla bla', CourantCategorie::CSR())
	],
	'catNames' => CourantCategorie::getEnumDescriptions()
]);

file_put_contents(__DIR__ . '/../courant.html', $courant);
