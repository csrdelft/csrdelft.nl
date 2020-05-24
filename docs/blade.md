# Blade

Zie [laravel.com/docs/5.6/blade](https://laravel.com/docs/5.6/blade) voor de officiele documentatie van Laravel en [EFTEC/BladeOne](https://github.com/EFTEC/BladeOne) voor de documentatie van het smaakje dat wij gebruiken.

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
