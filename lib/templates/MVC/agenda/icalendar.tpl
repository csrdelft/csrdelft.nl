BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//C.S.R. Delft/Webstek C.S.R. Delft//NL
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
{foreach from=$items item=item}{if $item instanceof Lid}{* geen verjaardagen hier *}{else}
BEGIN:VEVENT
UID:{$item->getUID()}
{if $item->isHeledag()}DTSTART;VALUE=DATE:{$item->getBeginMoment()|date_format:'%Y%m%d'}
{else}DTSTART;TZID=Europe/Amsterdam:{$item->getBeginMoment()|date_format:'%Y%m%dT%H%M%S'}
{/if}
{if $item->isHeledag()}DTEND;VALUE=DATE:{$item->getEindMoment()|date_format:'%Y%m%d'}
{else}DTEND;TZID=Europe/Amsterdam:{$item->getEindMoment()|date_format:'%Y%m%dT%H%M%S'}
{/if}
SUMMARY:{str_replace(';','\;',str_replace(',','\,',substr($item->getTitel(),0,60)))}
{if $item->getBeschrijving()}DESCRIPTION:{str_replace("\r",'',str_replace("\n",'\\n',str_replace(';','\;',str_replace(',','\,',substr($item->getBeschrijving(),0,60)))))}
{/if}
END:VEVENT
{/if}{/foreach}
END:VCALENDAR