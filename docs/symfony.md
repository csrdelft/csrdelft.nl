# Symfony

De stek draait op de Symfony stack. Kijk op [symfony.com](https://symfony.com) voor meer informatie over Symfony.

Kijk naar de [Best Practices](https://symfony.com/doc/current/best_practices.html) op de Symfony website.

## Kernel

Alle requests worden door de Kernel van Symfony verwerkt. Zie `lib/Kernel.php` voor de details, het is niet heel anders dan een normale Symfony installatie.

## Container

De Service container van symfony is beschikbaar. In controllers kan in functies gebruik worden gemaakt van DI door bepaalde types op te geven als parameters.

Met de `CsrDelft\common\ContainerFacade` kun je te pas en te onpas de container te pakken krijgen. Dit is om de overgang te vergemakkelijken. Het liefst wordt op alle plekken DI gebruikt, bijv door factories te bouwen die in de container zitten. Dit is wel iets voor een moment waarop je eigenlijk op alle plekken makkelijk bij de container kan zonder dat er teveel plumbing code geschreven moet worden.

## Router

De router van Symfony wordt gebruikt om routes te resolven. De YAML configuratie wordt hier voor gebruikt. Zie de YAML bestanden in de `config/routes` map.

## ORM

Voor alle onderdelen (behalve het Barsysteem) wordt Doctrine gebruikt als ORM. Zie [ORM](orm.md)


## Security

Voor inloggen wordt symfony-security gebruikt, zie [Security](security.md)
