---
layout: default
parent: Backend
nav_order: 1
title: Caching
---

# Caching

Met Memcached kun je de stek best wel wat versnellen. Hier wordt uitgelegd hoe je dit op Windows kan instellen. Voor linux kun je het waarschijnlijk zelf wel en anders kun je in de Docker configuratie kijken.

_NOOT: Deze cache is anders dan de cache die te vinden is in `var/cache/<env>`. De cache hier is alleen gebasseerd op de code en bevat bijvoorbeeld de annotaties van Symfony._

## Werking van de cache

Er zijn twee caches `cache.app` en `doctrine.orm.second_level_cache`, de eerste is in code te gebruiken en de tweede is intern voor Doctrine. De caches zijn geconfigureerd in `config/custom/memcache.yaml` en de configuratie wordt dus alleen ingeladen als memcache ondersteund wordt en geconfigureerd is.

Lees meer over de Doctrine Second Level Cache in de [docs](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/second-level-cache.html#the-second-level-cache). Het komt er op neer dat je een `@ORM\Cache(usage="NONSTRICT_READ_WRITE")` annotatie toevoegt aan een entity en dat dan alles automagisch werkt. Alleen de meest simpele case is geconfigureerd op dit moment (12-3-2020), dus geen regions, locks, etc.

De `cache.app` cache is vrij te gebruiken en in te laden door `Symfony\Contracts\Cache\CacheInterface` in de dependencies van je service te zetten, of door `cache.app` uit de service container te plukken. Zie `MenuItemRepository` voor een voorbeeld in het gebruik van de cache. De api is op zich best wel simpel, de uitdaging zit 'm in het netjes legen van de cache wanneer dat nodig is.

> There are only two hard things in Computer Science: cache invalidation and naming things.
>
> -- Phil Karlton

## Installeren van Memcached

Er is geen build van de laatste versie van Memcached voor windows beschikbaar, maar een oude versie is prima. Op Syrinx draait ook een best wel oude versie.

- [downloads.northscale.com/memcached-1.4.5-x86.zip](http://downloads.northscale.com/memcached-1.4.5-x86.zip)
- [downloads.northscale.com/memcached-1.4.5-amd64.zip](http://downloads.northscale.com/memcached-1.4.5-amd64.zip)

Je kan de executable uitvoeren en dan draait er een Memcached server op `localhost:11211`, zo simpel is het. Op Syrinx is memcached beschikbaar als unix socket in de data map, maar dit werkt niet echt op Windows.

Het kan handig zijn om Memcached als service te draaien, dan staat ie altijd aan en heb je er geen omkijken naar. Memcached draait standaard met een cache van 64MB en doet nauwelijks iets als hij niets te doen heeft. Je merkt er dus niets van.

Met [NSSM](https://nssm.cc) kun je een executable als Windows service installeren. Dit werkt heel goed met Memcached.

## Installeren van Memcache in PHP

Er zijn twee PHP extensies voor Memcached, namelijk [Memcached](https://www.php.net/manual/en/book.memcached.php) en [Memcache](https://www.php.net/manual/en/book.memcache.php). Memcached is nieuwer, dus deze willen we hebben.

Je kan `php_memcached.dll` en `libmemcached.dll` downloaden van https://github.com/lifenglsf/php_memcached_dll op linux kun je de extensie van pecl downloaden.

Drop de `php_memcached.dll` dll in de `ext` map in je php installatie (`C:\xampp\php\ext`, `C:\wamp64\bin\php7.x.x\ext`) en de `libmemcached.dll` aan de hoofdmap van php, waar ook `php.exe` staat. En voeg in php.ini (`C:\xampp\php\php.ini`, `C:\wamp64\bin\php7.x.x\php.ini`, ...) een regel toe:

```
extension=memcached
```

Start hierna Apache opnieuw op.

## Configureren van de cache

Voeg de volgende regels toe aan `.env.local`:

```
MEMCACHED_URL=memcached://localhost
```

Als je memcached op een unix socket draait (zoals op Syrinx gebeurt), voeg dan de volgende regels toe:

```
MEMCACHED_URL=memcached:///var/run/memcached.sock
```

Voor meer informatie over de mogelijke formaten van deze variabele, zie de [Memcached Adapter](https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html) documentatie.

Doordat `MEMCACHED_URL` een waarde heeft en de memcache extensie is geinstalleerd wordt de configuratie voor memcache geladen. Als het niet werkt controleer dan in phpinfo of het kopje memcache bestaat.

Als je in de profiler toolbar onder het doctrine icoontje cache hits / misses ziet staan weet je dat het werkt.

![Geen cache](https://i.imgur.com/iXvIu91.png)

Geen cache ingesteld

![Wel een cache](https://i.imgur.com/r7LmBAF.png)

Wel een cache ingesteld

## Flushen van de cache

Bij het veranderen van database of bij het veranderen van branches die ver uit elkaar liggen kan het zijn dat wat in de cache staat niet meer klopt. Dan moet de cache geflushed worden. Dit gebeurt normaal bij iedere deploy (de data in de cache bestaat dus niet lang en kan zo verdwenen zijn).

Om de cache te flushen kun je het `php bin/console stek:cache:flush` commando uitvoeren of de `Memcached` service vanuit taakbeheer opnieuw opstarten.

## De cache inspecteren

Een goede tool om de cache te inspecteren is [PHPMemcachedAdmin](https://github.com/elijaa/phpmemcachedadmin), hiermee kan je zien of de cache gebruikt wordt en in de cache zoeken.
