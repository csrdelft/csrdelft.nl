<div id="eetplan">
    <table class="eetplantable">
        <thead>
        <tr>
            <th>Novieten</th>
            @foreach($avonden as $avond)
                <th>{{ $avond }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($novieten as $noviet)
            <tr>
                <td>
                    <a href="{{ route('eetplan.noviet', ['profiel' => $noviet->noviet], false) }}">{{ $noviet->noviet->getNaam() }}</a>
                </td>

                @foreach($noviet->avonden as $woonoord)
                    <td><a href="'{{ route('eetplan.huis', ['id' => $woonoord->id]) }}">{{ $woonoord->naam }}</a></td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>