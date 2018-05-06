BEGIN:VCALENDAR
PRODID:-//C.S.R. Delft//C.S.R. agenda//NL
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-ORIGINAL-URL:{$smarty.const.CSR_ROOT}/agenda/
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
{foreach from=$items item=item}BEGIN:VEVENT
{if $item->isHeledag()}DTSTART;VALUE=DATE:{$item->getBeginMoment()|date_format:'%Y%m%d'|escape_ical:19}
{else}DTSTART;TZID=Europe/Amsterdam:{$item->getBeginMoment()|date_format:'%Y%m%dT%H%M%S'|escape_ical:30}
{/if}
{if $item->isHeledag()}DTEND;VALUE=DATE:{$item->getEindMoment()|date_format:'%Y%m%d'|escape_ical:17}
{else}DTEND;TZID=Europe/Amsterdam:{$item->getEindMoment()|date_format:'%Y%m%dT%H%M%S'|escape_ical:28}
{/if}
DTSTAMP:{$published}
UID:{$item->getUUID()|escape_ical:4}
CREATED:{$published}
DESCRIPTION:{$item->getBeschrijving()|escape_ical:12}
LAST-MODIFIED:{$published}
LOCATION:{$item->getLocatie()|escape_ical:9}
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:{$item->getTitel()|escape_ical:8}
TRANSP:TRANSPARENT
END:VEVENT
{/foreach}
END:VCALENDAR