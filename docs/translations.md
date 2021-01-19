# Vertalingen

De externe stek is ook beschikbaar in het Engels. Op de meeste plekken wordt de locale automatisch gebruikt. Het is ook mogelijk om de huidige locale op te halen. In controller functies kan dit door `Symfony\Component\HttpFoundation\Request` als argument te vragen en hier `getLocale` op aan te roepen. In overige services kun je `Symfony\Component\HttpFoundation\RequestStack` als argument vragen en hier `->getCurrentRequest()->getLocale()` op aanroepen.

Zie ook de documentatie van [`symfony/translation`](https://symfony.com/doc/current/translation.html) voor meer informatie.

## Vertalignen gebruiken
### Vertalingen in templates

In templates kunnen stukken tekst op twee verschillende manieren vertaald worden. Met `{% raw %}{% trans %}{% endraw %}` blokken en met de `trans` filter.

```html
<p>{% raw %}{% trans %}Deze tekst wordt vertaald{% endtrans %}{% endraw %}</p>

<p>{{ 'Deze tekst wordt vertaald'|trans }}</p>
```

### Vertalingen in code

Gebruik voor vertalingen in code de functie `trans` van `Symfony\Contracts\Translation\TranslatorInterface`.

```php
function __construct(\Symfony\Contracts\Translation\TranslatorInterface $translator) {
	$bericht = $translator->trans('Deze tekst wordt vertaald');
}
```

### Vertalingen in CMS paginas en menus

CMS paginas en menus worden op basis van de huidige locale geladen. De standaard locale is `nl`, voor deze locale wordt de pagina of het menu met de gevraagde naam geladen. Voor alle andere locales wordt gezocht of de pagina of het menu met de naam gevolgd door `_<locale>` bestaat, als dit het geval is wordt deze geladen.

Bijvoorbeeld voor de pagina `vereniging`. Voor locale `nl` wordt `vereniging` geladen. Voor locale `en` wordt `vereniging_en` geladen, als deze niet bestaat wordt `vereniging` geladen.

## Vertalingen bewerken

Gebruik het volgende commando om alle vertalingen die in de broncode gebruikt worden in het centrale vertalingen bestand te zetten:

```shell
symfony console translation:update en --force --domain=messages
```

Dit zorgt ervoor dat het bestand `translations/messages+intl-icu.en.xlf` wordt geupdate. Zorg er in dit bestand voor dat alle `<target>` tags gevuld zijn met de vertaalde teksten. (Als alles vertaald is kun je geen `__` meer vinden in het bestand.)

## Variabelen in vertalingen

Het is ook mogelijk om variabelen te gebruiken in vertalingen, bijvoorbeeld als er een woord is dat op basis van een variabele gezet wordt. Op deze manier heb je geen twee losse vertaling strings nodig

```html
<p>{% raw %}{% trans with {'naam': get_naam()} %}Hallo, {naam}{% endtrans %}{% endraw %}</p>
<p>{{ 'Hallo, {naam}'|trans({'naam': get_naam()}) }}</p>
```

```php
$vertaling = $translator->trans('Hallo, {naam}', ['naam' => get_naam()]);
```

Kijk ook in de documentatie van de [ICU messageformat](https://symfony.com/doc/current/translation/message_format.html).
