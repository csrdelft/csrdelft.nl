---
layout: default
parent: Backend
nav_order: 1
title: Enums
---

# Enums

Enums zijn vet handig als je van tevoren al weet welke waarden een specifiek veld mag hebben.

Met enums kun je type safe vaste waardes over en weer sturen en hoef je dus geen losse strings te gebruiken op plekken waar je weet dat er maar een paar verschillende waardes mogelijk zijn.

## Voorbeeld enum

De enum klasse kijkt naar alle `const` variabelen in de klasse, deze worden verschillende Enum waardes. Er worden ook functies gemaakt voor alle losse enum waardes. Je kan bijvoorbeeld hier onder `OntvangtContactueel::Nee()` aanroepen om naar de Nee waarde van de Enum te verwijzen.

```php
/**
 * @method static static Nee
 * @method static static Digitaal
 * @method static static Ja
 */
class OntvangtContactueel extends \CsrDelft\common\Enum
{
	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Ja => 'ja',
		self::Digitaal => 'ja, digitaal',
		self::Nee => 'nee',
	];
}
```

# Enums in Doctrine

Om doctrine je enum te laten snappen moet je een 'Type' er voor aanmaken, zie de `lib/common/Doctrine/Type` map voor voorbeelden. Hier onder zie je de meest simpele versie.

```php
class OntvangtContactueelType extends
	\CsrDelft\common\Doctrine\Type\Enum\EnumType
{
	public function getEnumClass(): string
	{
		return \CsrDelft\entity\OntvangtContactueel::class;
	}
	public function getName(): string
	{
		return 'enumOntvangtContactueel';
	}
}
```

Om deze enum te gebruiken zet je de type op het veld in je entity

```php
/**
 * @ORM\Column(type="enumontvangtcontactueel")
 * @var \CsrDelft\entity\OntvangtContactueel
 */
public $ontvangtcontactueel;
```
