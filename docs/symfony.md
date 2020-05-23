# Symfony

De stek draait op de Symfony stack.

## Kernel

Alle requests worden door de Kernel van Symfony verwerkt. Zie `lib/Kernel.php` voor de details, het is niet heel anders dan een normale Symfony installatie.

## Container

De Service container van symfony is beschikbaar. In controllers kan in functies gebruik worden gemaakt van DI door bepaalde types op te geven als parameters.

Met de `CsrDelft\common\ContainerFacade` kun je te pas en te onpas de container te pakken krijgen. Dit is om de overgang te vergemakkelijken. Het liefst wordt op alle plekken DI gebruikt, bijv door factories te bouwen die in de container zitten. Dit is wel iets voor een moment waarop je eigenlijk op alle plekken makkelijk bij de container kan zonder dat er teveel plumbing code geschreven moet worden.

## Router

Alles is omgebouwd naar de router van symfony, zie `config/routes`

## ORM

Doctrine is al ingebouwd, wordt nog maar op een paar plekken gebruikt (eetplan)
