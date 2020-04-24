{{-- Geen indent, want het maakt uit voor de gegenereerde bbcode --}}
@foreach($items as $item)
@if($item instanceof \CsrDelft\entity\profiel\Profiel) {{-- Geen verjaardagen --}}
@elseif($item instanceof \CsrDelft\entity\corvee\CorveeTaak) {{-- Geen corvee --}}
@else
{{strftime("%A %d-%m %H:%M", $item->getBeginMoment())}} [url={{CSR_ROOT}}/agenda/maand/{{strftime("%Y-%m", $item->getBeginMoment())}}]{{$item->getTitel()}}[/url]
@endif
@endforeach
