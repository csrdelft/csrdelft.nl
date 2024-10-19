<?php

namespace CsrDelft\command;

use CsrDelft\common\Mail;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccountService;
use CsrDelft\service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[
	AsCommand(
		name: 'stek:welkom',
		description: 'Stuur welkom mails naar novieten'
	)
]
class WelkomCommand extends Command
{
	public function __construct(
		private readonly string $emailPubCie,
		private readonly AccountRepository $accountRepository,
		private readonly AccountService $accountService,
		private readonly ProfielRepository $profielRepository,
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly MailService $mailService
	) {
		parent::__construct();
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output
	): int {
		$helper = $this->getHelper('question');
		$jaar = $helper->ask(
			$input,
			$output,
			new Question(
				'Welke lichting moet een welkom mail krijgen (vier cijfers). '
			)
		);

		if ($jaar == null && strlen($jaar) != 4 && !is_numeric($jaar)) {
			$output->writeln('Geen geldig jaar');

			return Command::FAILURE;
		}

		$praesesNaam = $helper->ask(
			$input,
			$output,
			new Question('Wat is de naam van de h.t. PubCie-Praeses? ')
		);

		if ($praesesNaam == null) {
			$output->writeln('Geen geldige naam');

			return Command::FAILURE;
		}

		# Tekst toe te voegen aan de mail als de novieten een abonnement op de maaltijd krijgen:
		# 	Je hebt een abonnement op de donderdagmaaltijd in Confide. Dit om jullie te stimuleren deel te nemen aan C.S.R. activiteiten en te integreren met leden. Als je inlogt op de webstek zie je meteen op de hoofdpagina een blokje waarin je jezelf kunt af- of aanmelden voor de eerstvolgende maaltijd.
		# 	Kun je niet op een maaltijd aanwezig zijn, meld je dan af op de webstek, voor omstreeks 15:00 op de dag van de maaltijd, want na dat tijdstip beginnen de koks met koken en wordt er op jou gerekend met boodschappen doen. Als je er na 15:00 achter komt dat je juist wel of juist niet aanwezig kunt zijn, bel dan even de koks of bel Confide. Met een goede reden kan je dan eventueel doorgestreept of toegevoegd worden op de maaltijdlijst.
		# 	Waarom is dit belangrijk? Omdat een maaltijd €3,50 kost en als je jezelf vergeet af te melden en niet komt, dit bedrag toch van je CiviSaldo wordt afgeschreven. Het is goed om naar maaltijden te gaan, maar als je niet kunt, meld je dan af! Dat scheelt je pieken.

		$novieten = $this->profielRepository->getNovietenVanLaatsteLidjaar($jaar);
		$numNovieten = count($novieten);

		if (
			!$helper->ask(
				$input,
				$output,
				new ConfirmationQuestion(
					"Er zijn {$numNovieten} novieten gevonden, doorgaan met mails versturen? [Yn] "
				)
			)
		) {
			return Command::SUCCESS;
		}

		foreach ($novieten as $profiel) {
			//			$url = $this->urlGenerator->generate('wachtwoord_aanvragen');
			$url = 'https://csrdelft.nl/wachtwoord/aanvragen';
			$tekst = <<<TEXT

Beste noviet {$profiel->voornaam},

Bij je lidmaatschap van C.S.R. hoort ook de mogelijkheid om in te loggen op de C.S.R.-webstek.

Via de webstek kun je onder andere:
- Berichten lezen en plaatsen op het forum
- Berichten plaatsen in de C.S.R.-courant, die wekelijks aan alle leden wordt verzonden
- Gegevens van andere leden opzoeken
- Aan/afmelden voor de maaltijd

Je hebt een abonnement op de donderdagmaaltijd in Confide. Dit om jullie te stimuleren deel te nemen aan C.S.R. activiteiten en te integreren met leden. Als je inlogt op de webstek zie je meteen op de hoofdpagina een blokje waarin je jezelf kunt af- of aanmelden voor de eerstvolgende maaltijd.
Kun je niet op een maaltijd aanwezig zijn, meld je dan af op de webstek, voor omstreeks 15:00 op de dag van de maaltijd, want na dat tijdstip beginnen de koks met koken en wordt er op jou gerekend met boodschappen doen. Als je er na 15:00 achter komt dat je juist wel of juist niet aanwezig kunt zijn, bel dan even de koks. Met een goede reden kan je dan eventueel doorgestreept of toegevoegd worden op de maaltijdlijst.
Waarom is dit belangrijk? Omdat een maaltijd €3,50 kost en als je jezelf vergeet af te melden en niet komt, dit bedrag toch van je CiviSaldo wordt afgeschreven. Het is goed om naar maaltijden te gaan, maar als je niet kunt, meld je dan af! Dat scheelt je pieken.

Gebruik je emailadres om in te loggen op de webstek: {$profiel->email}
Gebruik de eerste keer de [url={$url}]wachtwoord aanvragen[/url] functie om je eigen wachtwoord in te stellen.
Nadat je bent ingelogd kun een bijnaam instellen die je in plaats van je lidnummer kunt gebruiken om in te loggen.

Wanneer je problemen hebt met inloggen, of andere vragen over de webstek, kun je terecht bij de PubCie.
Stuur dan een e-mail naar: {$this->emailPubCie}

Met amicale groet,

{$praesesNaam},
h.t. PubCie-Praeses der Civitas Studiosorum Reformatorum
TEXT;
			$mail = new Mail(
				[$profiel->email => $profiel->voornaam],
				'Inloggegevens C.S.R.-webstek',
				$tekst
			);
			$mail->addBcc([$this->emailPubCie => 'PubCie C.S.R.']);
			$this->mailService->send($mail);

			if (!$this->accountRepository->existsUid($profiel->uid)) {
				// Maak een account aan voor deze noviet
				$this->accountService->maakAccount($profiel->uid);
			}

			$output->writeln($profiel->email . ' SEND!');
		}

		return Command::SUCCESS;
	}
}
