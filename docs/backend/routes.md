---
layout: default
parent: Backend
nav_order: 1
title: Routes
---

# Routes

Soms moet er een nieuwe pagina toegevoegd worden. Er zijn twee plekken waar je de pagina die je aan wil maken kan laten. In een al bestaande controller (zie de `lib/controller` map) of in een nieuwe controller. Als je pagina los staat van alle andere pagina's of als je een nieuw subsysteem aan het bouwen bent met een aantal pagina's is het het beste om een nieuwe controller te maken.

## Structuur van een controller

- Een controller bevindt zich in de `lib/controller` map in de repository.

- Een controller extend _meestal_ `CsrDelft\controller\AbstractController` hier krijg je wat gratis dingen er bij, zoals de `AbstractController` van Symfony en wat DataTable functies.

- De constructor van een controller verwijst naar repositories (`lib/repository`) en services (`lib/services`) die veel worden gebruikt in die specifieke controller. (bij grote uitzondering wordt er ook verwezen naar andere controllers, zie `ZoekController`).

In het begin is een controller een klasse met een lege constructor. Een controller is ook altijd een [Service](services.md).

```php
namespace CsrDelft\controller;

class MijnController extends AbstractController {
    public function __construct() {
    }
}
```

## Routes toevoegen

Een route is een omvorming van een URL naar een methode in een controller. Als er een request binnenkomt bij de stek wordt de juiste methode met de juiste parameters opgezocht om aan te roepen.

```php
namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MijnController extends AbstractController {
    public function __construct() {
    }

    /**
     * @return Response
     * @Route("/mijn/zien/{naam}", methods={"GET"})
     * @Auth(P_LOGGED_IN)
     */
    public function laatZien($naam) {
        return new Response("Mijn dingen " . $naam);
    }
}
```

Een route methode returned altijd een `Syfmony\Component\HttpFoundation\Response` of een `CsrDelft\view\ToResponse` instance. `ToResponse` is een handige extra optie die als wrapper wordt gebruikt als je niet zomaar een `Response` kan teruggeven.

In de route kun je parameters opgeven tussen accolades. Het is ook mogelijk om hier Dependency Injection te gebruiken als je een repository of service alleen voor een specifieke route nodig hebt.

### Annotaties

Er zijn een tweetal annotaties die belangrijk zijn bij het definieren van een route. De eerste `@Route` defineert waar de route te vinden is en wat de parameter zijn. De tweede `@Auth` geeft aan wat de minimale toegangseisen zijn, deze is door ons gebouwd om specifieke rechten makkelijk te kunnen afdwingen. Kijk bij [Permissies](permissies.md) voor meer info over wat je kan invullen als argument. Je hoeft hier geen aanhalingstekens ("") te gebruiken als je één specifieke permissie wil gebruiken, want alle permissies zijn ook constants (gedefinieerd in `lib/defines.include.php`).

### ParamConverter

De ParamConverter is een superhandige tool die uit [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html) komt. Met de ParamConverter kun je Doctrine entities als parameters in routes gebruiken. Er wordt er dan voor gezorgd dat je een entity uit de database krijgt als deze bestaat, of een 404 als deze niet bestaat.

Er wordt geprobeerd wat slimme dingen te doen om de juiste entity te vinden, over het algemeen werkt dit best wel goed. Het is ook mogelijk om de waarde nullable te maken en dus geen 404 te geven als er geen waarde wordt gegeven, hiervoor geef je de param in de route een default waarde en de param in de functie de default `null`.

```php
namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\profiel\Profiel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MijnController extends AbstractController {
    public function __construct() {
    }

    /**
     * @param Profiel $profiel
     * @return Response
     * @Route("/naam/{uid}", methods={"GET"})
     * @Auth(P_LOGGED_IN)
    */
    public function geefNaam(Profiel $profiel) {
        return new Response("Mijn dingen " . $profiel->getNaam());
    }
}
```

```

```
