---
layout: default
parent: Onderdelen
nav_order: 1
title: Formulier
---

# [WIP!] Formulier

Formulier is een klasse die gebruikt kan worden om formulieren op te bouwen en te valideren. Alle bestanden gerelateerd aan Formulier zijn te vinden in `lib/view/formulier`. Een formulier kan een hele pagina in beslag nemen, in een smarty template voorkomen of als popup gebruikt worden.

## Fields

Velden zijn te vinden in `FormElement.abstract.php`, `FormKnoppen.class.php`, `GetalVelden.class.php`, `InvoerVelden.class.php`, `KeuzeVelden.class.php` en `UploadVelden.class.php`.

### FormElement

#### HtmlComment

Een stuk losse HTML in het formulier.

| `$comment` | De HTML |
| ---------- | ------- |

```php
$fields[] = new HtmlComment('<strong>Dit gedeelte is belangrijk</strong>');
```

#### HtmlBbComment

Een stuk losse HTML met mogelijke BBCode in het formulier

| `$comment` | De HTML |
| ---------- | ------- |

```php
$fields[] = new HtmlBbComment('[b]Dit gedeelte is belangrijk[/b]');
```

#### FieldSet

Het begin van een nieuwe fieldset, alle volgende elementen zitten in deze FieldSet, totdat een veld `new HtmlComment('</fieldset>');` volgt.

| `$titel` | De titel van de fieldset |
| -------- | ------------------------ |

```php
$fields[] = new FieldSet('Adres');
...
$fields[] = new HtmlComment('</fieldset>');
```

#### SubKopje

Maak een subkopje aan, de standaard is `<h3>`. De eigenschap `$h` kan gebruikt worden om dit aan te passen.

| `$tekst` | De inhoud van de titel |
| -------- | ---------------------- |

```php
$subkopje = new SubKopje('Persoonlijke gegevens');
$subkopje->h = 2;
$fields[] = $subkopje;
```

#### CollapsableSubKopje

Maak een Subkopje met alles eronder collapsable, moet afgesloten worden met `new HtmlComment('</div>');`

|                         |                                              |
| ----------------------- | -------------------------------------------- |
| `$id`                   | De id gebruikt in de JavaScript              |
| `$titel`                | De titel van het kopje                       |
| `$collapsed = false`    | Of het subkopje collapsed is bij laden       |
| `$single = false`       | Of andere kopjes ook ingeklapt moeten worden |
| `$hover_click = false ` | Of het kopje moet uitklappen bij hover       |
| `$animate = true`       | Of het uitklappen geanimeerd moet zijn       |

```php
$fields[] = new CollapsableSubKopje('optioneel', 'Optionele gegevens', true);
...
$fields[] = new HtmlComment('</div>');
```

### InvoerVelden

#### (Required)TextField

Een normaal `<input type="text".../>` veld.

|                  |                                                            |
| ---------------- | ---------------------------------------------------------- |
| `$name`          | Naam van de input, gebruikt om waarde op te halen met POST |
| `$value`         | Waarde van de input                                        |
| `$description`   | Beschrijving, bij mouse-over                               |
| `$max_len = 255` | Maximale lengte van de input                               |
| `$min_len = 0`   | Minimale lengte van de input                               |
| `$model = null`  | _Ongebruikt_                                               |

```php
$fields[] = new TextField('voornaam', 'Voornaam', 'Vul hier je voornaam in', 255, 2);
$fields[] = new RequiredTextField('achternaam', 'Achternaam', 'Vul hier je achternaam in', 255, 2);
```

#### (Required)FileNameField

Zie [TextField](#requiredtextfield), valideert of een TextField een geldige bestandsnaam bevat.

```php
$fields[] = new FileNameField('foto', 'Foto', 'Geef de foto een naam');
$fields[] = new RequiredFileNameField('album', 'Album', 'Geef een albumnaam');
```

#### (Required)LandField

Zie [TextField](#requiredtextfield), geeft suggesties voor landen: 'Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten'.

```php
$fields[] = new LandField('geboorteland', 'Geboorteland', '');
$fields[] = new RequiredLandField('land', 'Land', '');
```

#### (Required)RechtenField

Zie [TextField](#requiredtextfield), geeft suggesties voor specifieke rechtengroepen en valideert deze.

```php
$fields[] = new RechtenField('maglezen', 'Mag lezen', '');
$fields[] = new RequiredRechtenField('magschrijven', 'Mag schrijven', '');
```

#### (Required)LidField

Zie [TextField](#requiredtextfield), geeft suggesties voor leden. Opties zijn 'leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'.

|                         |                              |
| ----------------------- | ---------------------------- |
| `$name`                 | Naam van de input            |
| `$value`                | Beginwaarde van de input     |
| `$description`          | Beschrijving, bij mouse-voer |
| `$zoekin = 'alleleden'` | Groep om in te zoeken        |

```php
$fields[] = new LidField('lid', 'Lid', '');
$fields[] = new RequiredLidField('noviet', 'Noviet', '', 'novieten');
```

|HtmlComment | $html | |
|HtmlBbComment
|FieldSet
|Subkopje
|CollapsableSubKopje
|
|(Required)TextField
|(Required)FileNameField
|(Required)EntityField
|StudieField
|(Required)EmailField
|(Required)UrlField
|(Required)UsernameField
|(Required)DuckField
|(Required)TextareaField
|(Required)WachtwoordField
|(Required)WachtwoordWijzigenField
|
|(Required)IntField
|(Required)BedragField
|(Required)TelefoonField
|(Required)FloatField
|
|(Required)ColorField
|(Required)SelectField
|MultiSelectField
|(Required)EntityDropDown
|(Required)WeekdagField
|(Required)VerticaleField
|(Required)KerkField
|(Required)RadioField
|(Required)GeslachtField
|(Required)JaNeeField
|(Required)DateField
|(Required)TimeField
|(Required)CheckboxField
|(Required)DateTimeField
|(Required)SterrenField
|
|(Required)FileField
|(Required)ImageField
|BestandBehouden
|UploadFileField
|ExistingFileField
|DownloadUrlField

## Voorbeeld

```PHP
class MijnForm extends Forumulier {
    function __construct(Voorbeeld $model) {
        parent::__construct($model, '/voorbeeld/', false, true);
        $fields[] = new RequiredTextField('voornaam', $model->voornaam, 'Voornaam');
        $fields[] = new TextField('achternaam', $model->voornaam, 'Achternaam');
        $fields[] = new RequiredTextField('verhaal', $model->verhaal, 'Verhaal');
        $fields['btn'] = new FormDefaultKnoppen();

        $this->addFields($fields);
    }
}
```

```PHP
class VoorbeeldController extends Controller {
    ...

    public function voorbeeld() {
        if ($this->isPosted()) {
            $voorbeeld = new Voorbeeld();
            $voorbeeld->voornaam = filter_input(INPUT_POST, 'voornaam', FILTER_SANITIZE_STRING);
            $voorbeeld->achternaam = filter_input(INPUT_POST, 'achternaam', FILTER_SANITIZE_STRING);
            $voorbeeld->verhaal = filter_input(INPUT_POST, 'verhaal', FILTER_SANITIZE_STRING);
            $form = new MijnForm($voorbeeld);
            if ($form->validate()) {
                VoorbeeldModel::instance()->create($voorbeeld);
                setMelding("Voorbeeld met succes aangemaakt", 1);
                $this->view = new MijnForm(new Voorbeeld());
            } else {
                $this->view = $form;
            }
        } else {
            $this->view = new MijnForm(new Voorbeeld());
        }
    }
}
```
