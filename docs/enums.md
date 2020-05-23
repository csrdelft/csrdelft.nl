# Enums
Enums zijn vet handig als je van te voren al weet welke waarden een specifiek veld mag hebben.

Er zijn op dit moment twee manieren om enums te doen, de oude en de nieuwe. De oude wordt door csrdelft/orm gebruikt en gebruikt strings om enumwaardes over en weer te sturen. De nieuwe wordt door doctrine gebruikt en gebruikt instances van de enum om waardes over en weer te sturen, de laatste is dus type safe.

## Voorbeeld enum
```php
class OntvangtContactueel extends Enum {
	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	public static function Nee(){
		return static::from(self::Nee);
	}

	public static function Digitaal() {
		return static::from(self::Digitaal);
	}

	public static function Ja() {
		return static::from(self::Ja);
	}

	/**
	 * @var string[]
	 */
	 protected static $mapChoiceToDescription = [
		self::Ja => 'ja',
		self::Digitaal => 'ja, digitaal',
		self::Nee => 'nee',
	];

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToChar = [
		self::Ja => 'J',
		self::Digitaal => 'D',
		self::Nee => '-',
	];
}
```

# Enums in Doctrine

Om doctrine je enum te laten snappen moet je een 'Type' er voor aanmaken, zie de `lib/common/Doctrine/Type` map voor voorbeelden. Hier onder zie je de meest simpele versie.

```php
class OntvangtContactueelType extends EnumType {
	protected $name = 'enumontvangtcontactueel';
	protected $enumClass = OntvangtContactueel::class;
}
```

Om deze enum te gebruiken zet je de type op het veld in je entity

```php
/**
 * @ORM\Column(type="enumontvangtcontactueel")
 * @var OntvangtContactueel
 */
public $ontvangtcontactueel;
```
