BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//C.S.R. Delft/Webstek C.S.R. Delft//NL
X-WR-CALNAME:C.S.R. Agenda
X-ORIGINAL-URL:{LoginModel::getAccount()->getICalLink()|escape_ical:15}
BEGIN:VTIMEZONE
TZID:Europe/Amsterdam
X-LIC-LOCATION:Europe/Amsterdam
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
{foreach from=$items item=item}BEGIN:VEVENT
UID:{$item->getUUID()|escape_ical:4}
{if $item->isHeledag()}DTSTART;VALUE=DATE:{$item->getBeginMoment()|date_format:'%Y%m%d'|escape_ical:19}
{else}DTSTART;TZID=Europe/Amsterdam:{$item->getBeginMoment()|date_format:'%Y%m%dT%H%M%S'|escape_ical:30}
{/if}
{if $item->isHeledag()}DTEND;VALUE=DATE:{$item->getEindMoment()|date_format:'%Y%m%d'|escape_ical:17}
{else}DTEND;TZID=Europe/Amsterdam:{$item->getEindMoment()|date_format:'%Y%m%dT%H%M%S'|escape_ical:28}
{/if}
SUMMARY:{$item->getTitel()|escape_ical:8}
{if $item->getLink()}URL:{$item->getLink()|external_url|escape_ical:4}
{/if}
{if $item->getLocatie()}LOCATION:{$item->getLocatie()|escape_ical:9}
{/if}
{if $item->getBeschrijving()}DESCRIPTION:{$item->getBeschrijving()|escape_ical:12}
{/if}
END:VEVENT
{/foreach}
END:VCALENDAR