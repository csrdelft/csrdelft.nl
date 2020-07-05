# Blade

Zie [laravel.com/docs/5.6/blade](https://laravel.com/docs/5.6/blade) voor de officiele documentatie van Laravel en [EFTEC/BladeOne](https://github.com/EFTEC/BladeOne) voor de documentatie van het smaakje dat wij gebruiken.

## Een Blade template maken

In de map `resources/views` staan alle blade templates, ze hebben de `.blade.php` extensie. De meest simpele blade template ziet er als volgt uit:

```
@extends('layout')

@section('titel', 'Mijn Pagina')

@section('content')
    <h1>Welkom op mijn pagina, {$naam}</h1>
    <p>Lees hier over mijn dingen</p>
@endsection
```

In principe extend je `resources/views/layout.blade.php` in je blade template. Hierdoor krijg je het menu, de zijbalk, de footer, et cetera. In de `content` section kun je dan je eigen html schrijven.

Je kan een Blade template in je controller gebruiken door de `view` functie te gebruiken. Het eerste argument van deze functie verwijst naar de template. De naam van de template is de path relatief aan `resources/views` waarbij iedere `/` is vervangen door een `.`. *Gebruik dus geen `.` in de naam van je template, dit zorgt voor problemen*

```php
return view('mijn_pagina', ['naam' => 'Mijn Naam']);
```

## Custom Blade Directives

Zie `CsrDelft\view\renderer\BladeRenderer` voor de implementatie.

### `@icon`

Dunne wrapper om `Icon::getTag`, accepteert dezelfde argumenten.

### `@cycle`

Te gebruiken in een Blade `@foreach`, gebruik om waarden af te wisselen. Iedere keer als de functie wordt aangeroepen wordt de volgende waarde gereturned.

### `@link`

Shortcut voor `link_for` in `common.view.functions.php`, accepteert de volgende argumenten:
 * `title`: De tekst van de link
 * `href`: De url van de link
 * `class`: De className van de link
 * `activeClass`: De className van de link als de link naar deze pagina verwijst.

Bijvoorbeeld `@link('Overzicht', '/fiscaat', 'nav-link', 'active')`

### `@stylesheet`

Wordt bij het compileren voor productie vervangen door de html van de juiste stylesheet, dan hoeft dit op een later moment niet meer te gebeuren.

### `@script`

Zelfde als `@stylsheet`.
