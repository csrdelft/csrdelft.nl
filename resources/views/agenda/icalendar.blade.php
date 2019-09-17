<?php /** @var \CsrDelft\model\entity\agenda\Agendeerbaar $item */
?>BEGIN:VCALENDAR
PRODID:-//C.S.R. Delft//C.S.R. agenda//NL
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-ORIGINAL-URL:{!!CSR_ROOT!!}/agenda
X-WR-CALNAME:C.S.R. agenda
X-WR-TIMEZONE:Europe/Amsterdam
X-WR-CALDESC:
BEGIN:VTIMEZONE
TZID:Europe/Amsterdam
X-LIC-LOCATION:Europe/Amsterdam
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
@foreach($items as $item)
BEGIN:VEVENT
@if($item->isHeledag())
DTSTART;VALUE=DATE:{!!escape_ical(strftime("%Y%m%d", $item->getBeginMoment()), 19)!!}
@else
DTSTART;TZID=Europe/Amsterdam:{!!escape_ical(strftime("%Y%m%dT%H%M%S", $item->getBeginMoment()), 30)!!}
@endif
@if($item->isHeledag())
DTEND;VALUE=DATE:{!!escape_ical(strftime("%Y%m%d", $item->getEindMoment()), 17)!!}
@else
DTEND;TZID=Europe/Amsterdam:{!!escape_ical(strftime("%Y%m%dT%H%M%S", $item->getEindMoment()), 28)!!}
@endif
DTSTAMP:{!!$published!!}
UID:{!!escape_ical($item->getUUID(), 4)!!}
CREATED:{!!$published!!}
DESCRIPTION:{!!escape_ical($item->getBeschrijving(), 12)!!}
LAST-MODIFIED:{!!$published!!}
LOCATION:{!!escape_ical($item->getLocatie(), 9)!!}
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:{!!escape_ical($item->getTitel(), 8)!!}
@if($item->isTransparant())
TRANSP:TRANSPARENT
@else
TRANS:OPAQUE
@endif
END:VEVENT
@endforeach
END:VCALENDAR
