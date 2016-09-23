$(function () {
    var $novieten = $('.novietentabel'),
        $eetplan = $('.eetplantabel'),
        eetplan = null;

    function bouwEetplanTabel($novieten, $eetplan, data) {
        $eetplan.empty();
        $novieten.empty();

        var $eetplanHeader = $('<tr>'),
            $novietenHeader = $('<tr><th>Novieten</th></tr>'),
            $geenEetplan = $('<td>').attr('class', 'leeg').text(" ");

        $novieten.append($novietenHeader);

        var avonden = [];

        data.avonden.sort();

        $.each(data.avonden, function (key, avond) {
            avonden.push(avond);
            $eetplanHeader.append($('<th>').text(avond));
        });

        $eetplan.append($eetplanHeader);

        $.each(data.novieten, function (key, noviet) {
            var $row = $('<tr>').append('<td><a href="/eetplan/noviet/' + noviet.uid + '">' + noviet.naam + '</a></td>'),
                $avonden = $('<tr>');

            var avondLijst = [];
            $.each(avonden, function () {
                avondLijst.push($geenEetplan.clone());
            });
            $.each(noviet.avonden, function (key, avond) {
                var index = avonden.indexOf(avond.datum);
                avondLijst[index] = $('<td>').html('<a href="/eetplan/huis/' + avond.woonoord_id + '">' + avond.woonoord + '</a>');
            });

            $.each(avondLijst, function (key, avond) {
                $avonden.append(avond);
            });

            $eetplan.append($avonden);
            $novieten.append($row);
        });
    }

    $.ajax({
        method: 'GET',
        url: '/eetplan/json'
    }).done(function (data) {
        bouwEetplanTabel($novieten, $eetplan, data);
    });
});