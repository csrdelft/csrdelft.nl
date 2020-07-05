# Migraties

*Je hebt dit document pas nodig als je zelf migraties gaat maken. Tot die tijd moet je je er niet te druk over maken.*

[Database migraties](https://en.wikipedia.org/wiki/Schema_migration) zijn een oplossing voor de problemen die ontstaan als je veranderingen wil aanbrengen aan de database, maar dit op een reproduceerbare manier wil doen en dit ook wil communiceren met andere mensen die aan de een project ontwikkelen.

Voor migraties gebruiken we [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/2.2/index.html) en de [DoctrineMigrationsBundle](https://symfony.com/doc/2.2.x/bundles/DoctrineMigrationsBundle/index.html) kijk eerst naar de documentatie van DoctrineMigrationsBundle als je meer informatie wil hebben.


## Migraties uitvoeren

Migraties worden uitgevoerd als je het `composer update-dev` commando uitvoert, je kan ook los de nog niet uitgevoerde migraties uitvoeren door het volgende commando uit te voeren:

```bash
$ php bin/console doctrine:migrations:migrate
```

Dit geeft terug hoeveel migraties er uitgevoerd zijn en of het gelukt is.

Voer het volgende commando uit als je meer informatie wil weten over de status van de migraties.

```bash
$ php bin/console doctrine:migrations:status
```

## Een migratie maken

Als je code hebt geschreven waar de database voor moet worden veranderd, bijvoorbeeld als je een kolom hebt toegevoegd of als je een nieuwe tabel nodig hebt moet je een migratie gebruiken om deze veranderingen te beschrijven.

Er zijn twee manieren om een migratie te maken, zelf schrijven of laten genereren.


### Een migratie zelf schrijven

Voer het volgende commando uit om een migratie bestand te maken.

```bash
$ php bin/console doctrine:migrations:generate
```

In de map `db/doctrine_migrations` is nu een lege migratie gemaakt. Kijk in de Doctrine Migrations documentatie voor meer info over het schrijven van migraties. In principe kun je platte SQL schrijven.

### Een migratie laten genereren


Je kan een migratie laten genereren op basis van de veranderingen die je hebt gemaakt. Hier voor voer je het volgende commando uit:

```bash
$ php bin/console doctrine:migrations:diff
```

In de map `db/docrine_migrations` wordt nu een nieuwe migratie aangemaakt. Kijk in het gegenereerde bestand of wat er in staat zinnig is.

## Een losse migratie uitvoeren en terugdraaien

Om een losse migratie uit te voeren kun je het volgende commando uitvoeren.

```bash
$ php bin/console doctrine:migrations:migrate next
```

Om een migratie terug te draaien (om te testen of dat werkt of bijvoorbeeld om veranderingen te kunnen maken aan je migratie):

```bash
$ php bin/console doctrine:migrations:migrate prev
```


