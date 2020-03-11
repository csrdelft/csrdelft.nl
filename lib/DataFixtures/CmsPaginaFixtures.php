<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\CmsPagina;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CmsPaginaFixtures extends Fixture {
	public function load(ObjectManager $manager) {
		$legePagina = new CmsPagina();

		$legePagina->inhoud = '';
		$legePagina->laatst_gewijzigd = date_create_immutable();
		$legePagina->titel = '';
		$legePagina->naam = '';
		$legePagina->inline_html = false;
		$legePagina->rechten_bekijken = P_PUBLIC;
		$legePagina->rechten_bewerken = '';

		$manager->persist($legePagina);

		$geenToegangPagina = new CmsPagina();

		$geenToegangPagina->naam = '403';
		$geenToegangPagina->titel = 'Geen toegang';
		$geenToegangPagina->inhoud = <<<BB
[h=1]Geen toegang[/h]
U heeft helaas niet genoeg rechten om deze pagina te bekijken, of er is een fout opgetreden die bij legaal gebruik van de website niet voor zou moeten kunnen komen.

Log in als gebruiker van de website met behulp van het inlogvakje rechtsboven op de pagina. Neem voor meer informatie contact op met de [email=pubcie@csrdelft.nl spamsafe=true]PubCie[/email].
BB;
		$geenToegangPagina->laatst_gewijzigd = date_create_immutable();
		$geenToegangPagina->rechten_bekijken = P_PUBLIC;
		$geenToegangPagina->rechten_bewerken = P_ADMIN;
		$geenToegangPagina->inline_html = false;

		$manager->persist($geenToegangPagina);

		$nietGevondenPagina = new CmsPagina();

		$nietGevondenPagina->naam = '404';
		$nietGevondenPagina->titel = 'Niet gevonden';
		$nietGevondenPagina->inhoud = <<<BB
[h=1]Pagina niet gevonden[/h]

De pagina die u zoekt kan helaas niet worden gevonden.
BB;
		$nietGevondenPagina->laatst_gewijzigd = date_create_immutable();
		$nietGevondenPagina->rechten_bekijken = P_PUBLIC;
		$nietGevondenPagina->rechten_bewerken = P_ADMIN;
		$nietGevondenPagina->inline_html = false;

		$manager->persist($nietGevondenPagina);

		$thuisPagina = new CmsPagina();
		$thuisPagina->naam = 'thuis';
		$thuisPagina->titel = 'Vereniging van Christenstudenten';
		$thuisPagina->inhoud = <<<BB
[h=1]Civitas Studiosorum Reformatorum Delft[/h]

Dit is de voorpagina.
BB;
		$thuisPagina->laatst_gewijzigd = date_create_immutable();
		$thuisPagina->rechten_bekijken = P_PUBLIC;
		$thuisPagina->rechten_bewerken = 'P_ADMIN,Bestuur';
		$thuisPagina->inline_html = true;

		$manager->persist($thuisPagina);

		$manager->flush();
	}
}
