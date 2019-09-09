<?php
/** @var CsrDelft\model\entity\profiel\Profiel $profiel */
?>
BEGIN:VCARD
VERSION:3.0
FN;CHARSET=UTF-8:{{$profiel->getNaam('volledig')}}
N;CHARSET=UTF-8:{{($profiel->tussenvoegsel?$profiel->tussenvoegsel.' ':'').$profiel->achternaam}};{{$profiel->voornaam}};;;
@if(is_zichtbaar($profiel, 'nickname'))
NICKNAME;CHARSET=UTF-8:{{$profiel->nickname}}
@endif
@if(is_zichtbaar($profiel, 'geslacht'))
GENDER:{{['m' => 'M', 'v' => 'F'][$profiel->geslacht]}}
@endif
UID;CHARSET=UTF-8:{{$profiel->uid}}
@if(is_zichtbaar($profiel, 'gebdatum'))
BDAY:{{date('Ymd', strtotime($profiel->gebdatum))}}
@endif
@if(is_zichtbaar($profiel, 'email'))
EMAIL;CHARSET=UTF-8;type=HOME,INTERNET:{{$profiel->email}}
@endif
@if(is_zichtbaar($profiel, 'profielfoto', 'intern'))
PHOTO;ENCODING=BASE64;TYPE=JPEG:{{base64_encode(file_get_contents($profiel->getPasfotoInternalPath()))}}
@endif
@if(is_zichtbaar($profiel, 'mobiel'))
TEL;TYPE=CELL:{{$profiel->mobiel}}
@endif
@if(is_zichtbaar($profiel, 'telefoon'))
TEL;TYPE=HOME,VOICE:{{$profiel->telefoon}}
@endif
@if(is_zichtbaar($profiel, ['adres', 'woonplaats', 'postcode', 'land']))
ADR;CHARSET=UTF-8;TYPE=HOME:;;{{$profiel->adres}};{{$profiel->woonplaats}};;{{$profiel->postcode}};{{$profiel->land}}
@endif
REV:{{date('c')}}
END:VCARD
