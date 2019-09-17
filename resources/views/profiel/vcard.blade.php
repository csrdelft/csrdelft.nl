<?php
/** @var CsrDelft\model\entity\profiel\Profiel $profiel */
?>
BEGIN:VCARD
VERSION:3.0
FN;CHARSET=UTF-8:{!!escape_ical($profiel->getNaam('volledig'), 0)!!}
N;CHARSET=UTF-8:{!!escape_ical(($profiel->tussenvoegsel?$profiel->tussenvoegsel.' ':'').$profiel->achternaam, 0)!!};{!!escape_ical($profiel->voornaam, 0)!!};;;
@if(is_zichtbaar($profiel, 'nickname'))
NICKNAME;CHARSET=UTF-8:{!!escape_ical($profiel->nickname, 0)!!}
@endif
@if(is_zichtbaar($profiel, 'geslacht'))
GENDER:{!!escape_ical(['m' => 'M', 'v' => 'F'][$profiel->geslacht], 0)!!}
@endif
UID;CHARSET=UTF-8:{!!escape_ical($profiel->uid, 0)!!}
@if(is_zichtbaar($profiel, 'gebdatum'))
BDAY:{!!escape_ical(date('Ymd', strtotime($profiel->gebdatum)), 0)!!}
@endif
@if(is_zichtbaar($profiel, 'email'))
EMAIL;CHARSET=UTF-8;type=HOME,INTERNET:{!!escape_ical($profiel->email, 0)!!}
@endif
@if(is_zichtbaar($profiel, 'profielfoto', 'intern'))
PHOTO;ENCODING=BASE64;TYPE=JPEG:{!!escape_ical(base64_encode(file_get_contents($profiel->getPasfotoInternalPath())), 0)!!}
@endif
@if(is_zichtbaar($profiel, 'mobiel'))
TEL;TYPE=CELL:{!!escape_ical($profiel->mobiel, 0)!!}
@endif
@if(is_zichtbaar($profiel, 'telefoon'))
TEL;TYPE=HOME,VOICE:{!!escape_ical($profiel->telefoon, 0)!!}
@endif
@if(is_zichtbaar($profiel, ['adres', 'woonplaats', 'postcode', 'land']))
ADR;CHARSET=UTF-8;TYPE=HOME:;;{!!escape_ical($profiel->adres, 0)!!};{!!escape_ical($profiel->woonplaats, 0)!!};;{!!escape_ical($profiel->postcode, 0)!!};{!!escape_ical($profiel->land, 0)!!}
@endif
REV:{!!escape_ical(date('c'), 0)!!}
END:VCARD
