---
layout: default
parent: Introductie
nav_order: 2
title: Symfony
---

# Symfony

De stek draait op de Symfony stack. Kijk op [symfony.com](https://symfony.com) voor meer informatie over Symfony.

Kijk in de [Getting Started](https://symfony.com/doc/current/index.html) categorie van de Symfony documentatie, hier staat uitgelegd hoe je dingen met moet doen. De [Charming Development in Symfony 5](https://symfonycasts.com/screencast/symfony) screencasts zijn ook een goed startpunt. De meeste dingen zijn een-op-een toe te passen op de stek.

Als je een deep-dive in Symfony wil kun je het boek [Symfony5: The Fast Track](https://symfony.com/book) lezen (gratis te lezen, ook in het Nederlands). Dit boek gaat veel verder dan wat we nu met de stek doen en gaat ook in op Symfony cloud.

Kijk naar de [Best Practices](https://symfony.com/doc/current/best_practices.html) op de Symfony website.

We gebruiken **YAML** voor configuratie en **Annotations** voor routes en entities.

## Afwijkingen van Symfony

Omdat Symfony in stappen is/wordt geintroduceerd in de stek is (nog) niet alles precies volgens het boekje.

- Geen `symfony/assets`, dit moet nog gefixt worden
- Geen `Webpack Encore`, de webpack setup die we hebben werkt prima.
- Geen `symfony/mailer`, misschien nog overstappen, huidige mailoplossing werkt nog prima.
- Nog maar op een paar plekken `symfony/form`, overstap gaat stroef, een aantal formfields moeten hier nog naar geport worden
- Geen `symfony/ldap`, eigen implementatie, zou misschien vervangen kunnen worden.
- Geen Symfony flash messages, eigen implementatie met `getMelding/setMelding`, moet nog omgebouwd worden.

## Kernel

Alle requests worden door de Kernel van Symfony verwerkt. Zie `lib/Kernel.php` voor de details, het is niet heel anders dan een normale Symfony installatie.

## Container

De Service container van symfony is beschikbaar. In controllers kan in functies gebruik worden gemaakt van DI door bepaalde types op te geven als parameters.

Met de `CsrDelft\common\ContainerFacade` kun je te pas en te onpas de container te pakken krijgen. Dit is om de overgang te vergemakkelijken. Het liefst wordt op alle plekken DI gebruikt, bijv door factories te bouwen die in de container zitten. Dit is wel iets voor een moment waarop je eigenlijk op alle plekken makkelijk bij de container kan zonder dat er teveel plumbing code geschreven moet worden.

Zie [Services](../backend/services.md) voor meer info over de container.

## Router

De router van Symfony wordt gebruikt om routes te resolven. De YAML configuratie wordt hier voor gebruikt. Zie de YAML bestanden in de `config/routes` map.

## ORM

Voor alle onderdelen (behalve het Barsysteem) wordt Doctrine gebruikt als ORM. Zie [ORM](../backend/orm.md)

## Security

Voor inloggen wordt symfony-security gebruikt, zie [Security](../backend/security.md)
